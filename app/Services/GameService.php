<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\BotUser;
use App\Models\GameSetting;
use App\Models\XpEvent;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

/**
 * Очки, уровни и правила доступа. Все начисления идут через журнал xp_events,
 * поэтому очки всегда можно пересчитать, а разовые награды не задваиваются.
 */
class GameService
{
    // ───────── режим игры ─────────
    // off      — игра выключена, кабинет как до игры (по умолчанию)
    // waiting  — экран ожидания, разделы закрыты
    // live     — игра запущена

    public const MODE_OFF = 'off';
    public const MODE_WAITING = 'waiting';
    public const MODE_LIVE = 'live';

    /**
     * Режим хранится в маленьком файле, а не в базе: его читает каждая страница кабинета
     * (меню, доступ), и лишний запрос в БД на каждый переход был бы ничем не оправдан.
     */
    private ?string $modeMemo = null;

    public static function modeFile(): string
    {
        return (string) config('game.mode_file');
    }

    public function mode(): string
    {
        if ($this->modeMemo !== null) {
            return $this->modeMemo;
        }

        $mode = self::MODE_OFF;
        $file = self::modeFile();
        if (is_file($file)) {
            $data = json_decode((string) @file_get_contents($file), true);
            $mode = is_array($data) ? (string) ($data['mode'] ?? self::MODE_OFF) : self::MODE_OFF;
        }

        return $this->modeMemo = in_array($mode, [self::MODE_OFF, self::MODE_WAITING, self::MODE_LIVE], true) ? $mode : self::MODE_OFF;
    }

    public function setMode(string $mode): void
    {
        $mode = in_array($mode, [self::MODE_OFF, self::MODE_WAITING, self::MODE_LIVE], true) ? $mode : self::MODE_OFF;
        $file = self::modeFile();

        if (! is_dir(dirname($file))) {
            @mkdir(dirname($file), 0775, true);
        }
        file_put_contents($file, json_encode(['mode' => $mode, 'changed_at' => now()->toIso8601String()]), LOCK_EX);

        $this->modeMemo = $mode;
    }

    /** Игра включена (ожидание или запуск): иначе её нигде не видно. */
    public function isEnabled(): bool
    {
        return $this->mode() !== self::MODE_OFF;
    }

    public function isLaunched(): bool
    {
        return $this->mode() === self::MODE_LIVE;
    }

    public function setLaunched(bool $on): void
    {
        $this->setMode($on ? self::MODE_LIVE : self::MODE_WAITING);
    }

    /**
     * Стереть все данные игры (очки, факты, нити, квесты). Флаги приватности участников не трогаем.
     */
    public function wipeData(): void
    {
        DB::transaction(function () {
            foreach (['quest_reports', 'quest_attempts', 'connections', 'xp_events', 'quests', 'profile_facts'] as $table) {
                DB::table($table)->delete();
            }
            DB::table('bot_users')->update(['xp' => 0, 'level' => 1, 'quest_locked_until' => null, 'last_reminded_at' => null]);
        });
    }

    // ───────── уровни ─────────

    /** @return array<int, array<string, mixed>> */
    public function levels(): array
    {
        return config('game.levels');
    }

    public function levelForXp(int $xp): int
    {
        $level = 1;
        foreach ($this->levels() as $n => $def) {
            if ($xp >= $def['from']) {
                $level = $n;
            }
        }

        return $level;
    }

    public function maxLevel(): int
    {
        return (int) max(array_keys($this->levels()));
    }

    /**
     * Данные уровня для интерфейса (на нужном языке).
     *
     * @return array{n: int, name: string, color: string, from: int}
     */
    public function levelInfo(int $level, string $lang = 'ru'): array
    {
        $def = $this->levels()[$level] ?? $this->levels()[1];

        return ['n' => $level, 'name' => $def['name'][$lang] ?? $def['name']['ru'], 'color' => $def['color'], 'from' => $def['from']];
    }

    /** Данные для полосы прогресса. */
    public function progress(BotUser $user): array
    {
        $cur  = $this->levelForXp($user->xp);
        $next = $cur < $this->maxLevel() ? $this->levels()[$cur + 1]['from'] : null;
        $from = $this->levels()[$cur]['from'];

        return [
            'xp'        => $user->xp,
            'level'     => $cur,
            'next_from' => $next,
            'percent'   => $next ? (int) round(min(100, ($user->xp - $from) / max(1, $next - $from) * 100)) : 100,
        ];
    }

    // ───────── доступ ─────────

    public function isStaff(BotUser $user): bool
    {
        return $user->is_moderator;
    }

    /** Можно ли открыть раздел: people | search | matches | opportunities. */
    public function canAccess(BotUser $user, string $feature): bool
    {
        if (! $this->isEnabled() || $this->isStaff($user)) {
            return true;
        }
        if (! $this->isLaunched()) {
            return false;
        }

        return $user->level >= (int) config("game.access.{$feature}", 1);
    }

    /** С какого уровня раздел открыт (для подсказок «откроется на уровне …»). */
    public function levelNeeded(string $feature): int
    {
        return (int) config("game.access.{$feature}", 1);
    }

    /** Видит ли зритель поле профиля другого участника. */
    public function canSee(BotUser $viewer, BotUser $target, string $field): bool
    {
        if (! $this->isEnabled() || $viewer->id === $target->id || $this->isStaff($viewer)) {
            return true;
        }

        return $viewer->level >= (int) config("game.visibility.{$field}", 1);
    }

    // ───────── очки ─────────

    /**
     * Начислить очки. Повтор с тем же (type, ref) игнорируется.
     * Возвращает номер нового уровня, если он вырос, иначе null.
     */
    public function award(BotUser $user, string $type, int $points, string $ref = '', array $meta = []): ?int
    {
        if ($points === 0 || ! $this->isEnabled()) {
            return null; // выключенная игра очков не начисляет
        }

        try {
            return DB::transaction(function () use ($user, $type, $points, $ref, $meta) {
                XpEvent::create(['bot_user_id' => $user->id, 'type' => $type, 'ref' => $ref, 'points' => $points, 'meta' => $meta ?: null]);

                $fresh  = BotUser::lockForUpdate()->find($user->id);
                $before = $fresh->level;
                $fresh->xp    = max(0, $fresh->xp + $points);
                $fresh->level = $this->levelForXp($fresh->xp);
                $fresh->save();

                $user->xp    = $fresh->xp;
                $user->level = $fresh->level;

                if ($fresh->level > $before) {
                    XpEvent::create(['bot_user_id' => $user->id, 'type' => 'level_up', 'ref' => (string) $fresh->level, 'points' => 0]);

                    return $fresh->level;
                }

                return null;
            });
        } catch (QueryException $e) {
            // уникальный ключ: такая награда уже была выдана
            if (str_contains($e->getMessage(), 'Duplicate') || str_contains($e->getMessage(), 'UNIQUE') || str_contains($e->getMessage(), 'Integrity')) {
                return null;
            }
            throw $e;
        }
    }

    /**
     * Награды за заполненность профиля. Безопасно вызывать сколько угодно раз.
     * Очки не отнимаются, если поле потом очистили.
     */
    public function syncProfileRewards(BotUser $user): ?int
    {
        $xp  = config('game.xp');
        $up  = null;
        $pick = fn (?int $new) => $up = $new ?? $up;

        if ($user->avatar_path) {
            $pick($this->award($user, 'profile_avatar', $xp['profile_avatar']));
        }
        if (mb_strlen(trim((string) $user->description)) >= 20) {
            $pick($this->award($user, 'profile_description', $xp['profile_description']));
        }
        if (mb_strlen(trim((string) $user->expectation)) >= 20) {
            $pick($this->award($user, 'profile_expectation', $xp['profile_expectation']));
        }
        if ($user->region) {
            $pick($this->award($user, 'profile_region', $xp['profile_region']));
        }
        foreach ($user->facts()->limit((int) config('game.max_facts'))->get() as $fact) {
            $pick($this->award($user, 'fact', $xp['fact'], (string) $fact->id));
        }

        return $up;
    }

    /** Сколько одобренных участников с заполненным «о себе» (для экрана ожидания). */
    public function readyParticipants(): int
    {
        $fn = DB::connection()->getDriverName() === 'sqlite' ? 'length' : 'char_length';

        return BotUser::approved()->whereNotNull('description')->whereRaw("{$fn}(description) >= 20")->count();
    }
}
