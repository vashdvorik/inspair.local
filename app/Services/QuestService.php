<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\BotUser;
use App\Models\Connection;
use App\Models\ProfileFact;
use App\Models\Quest;
use App\Models\QuestAttempt;
use App\Services\Ai\OpenRouter;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Квесты «угадай, кто это»: составление вопросов ИИ, автопроверка, выдача и ответы.
 */
class QuestService
{
    public function __construct(
        private readonly OpenRouter $ai,
        private readonly GameService $game,
    ) {}

    // ───────────────────────── генерация ─────────────────────────

    /** Участники, которых можно использовать в квестах. */
    public function eligibleUsers(): Collection
    {
        return BotUser::approved()->with('facts')
            ->where('quests_opt_out', false)
            ->get()
            ->filter(fn (BotUser $u) => $this->sourceText($u) !== '')
            ->values();
    }

    /** Нужно ли пополнять пул квестов. */
    public function poolDeficit(): int
    {
        $users  = $this->eligibleUsers()->count();
        if ($users < 3) {
            return 0;
        }
        $target = max((int) config('game.pool_min'), $users * (int) config('game.pool_per_user'));

        return max(0, $target - Quest::active()->count());
    }

    /** Создать до $count новых квестов. Возвращает, сколько создано. */
    public function generate(int $count): int
    {
        $users = $this->eligibleUsers();
        if ($users->count() < 3) {
            return 0;
        }

        $created = 0;
        $tries   = 0;
        while ($created < $count && $tries < $count * 3) {
            $tries++;
            try {
                if ($this->generateOne($users)) {
                    $created++;
                }
            } catch (\Throwable $e) {
                Log::warning('Quest generation failed', ['error' => $e->getMessage()]);
                if (str_contains($e->getMessage(), 'OPENROUTER') || str_contains($e->getMessage(), 'лимит') || str_contains($e->getMessage(), 'отключена')) {
                    break; // нет ключа или лимит — дальше пробовать бессмысленно
                }
            }
        }

        return $created;
    }

    private function generateOne(Collection $users): bool
    {
        // субъект: тот, у кого меньше всего активных квестов
        $counts  = Quest::active()->select('subject_user_id', DB::raw('count(*) c'))->groupBy('subject_user_id')->pluck('c', 'subject_user_id');
        $subject = $users->sortBy(fn (BotUser $u) => [$counts[$u->id] ?? 0, random_int(0, 1000)])->first();

        $fact = $this->pickFact($subject);
        if ($fact === null) {
            return false;
        }

        // два отвлекающих варианта, лучше из того же региона
        $others = $users->where('id', '!=', $subject->id)->shuffle();
        $sameRegion = $subject->region ? $others->where('region', $subject->region) : collect();
        $distractors = $sameRegion->concat($others)->unique('id')->take(2)->values();
        if ($distractors->count() < 2) {
            return false;
        }

        $draft = $this->ai->json('quest_generate', [
            ['role' => 'system', 'content' => $this->generatePrompt()],
            ['role' => 'user', 'content' => "Факт об участнике:\n«{$fact['text']}»"],
        ]);

        $question    = $this->i18n($draft['question'] ?? null);
        $explanation = $this->i18n($draft['explanation'] ?? null);
        if (! $question || ! $explanation) {
            return false;
        }

        if (! $this->verify($question['ru'], $subject, $distractors)) {
            return false;
        }

        $ids = $distractors->pluck('id')->push($subject->id)->shuffle()->values()->all();

        Quest::create([
            'fact_id'         => $fact['id'],
            'subject_user_id' => $subject->id,
            'option_ids'      => $ids,
            'question'        => $question,
            'explanation'     => $explanation,
            'status'          => Quest::ACTIVE,
        ]);

        return true;
    }

    /** @return array{id: int|null, text: string}|null */
    private function pickFact(BotUser $user): ?array
    {
        $used = Quest::where('subject_user_id', $user->id)->whereNotNull('fact_id')->pluck('fact_id')->all();
        $fact = $user->facts()->whereNotIn('id', $used)->inRandomOrder()->first();
        if ($fact) {
            return ['id' => $fact->id, 'text' => $fact->body];
        }

        $about = trim((string) $user->description);
        if ($about !== '' && ! Quest::where('subject_user_id', $user->id)->whereNull('fact_id')->exists()) {
            return ['id' => null, 'text' => $about];
        }

        return null;
    }

    public function sourceText(BotUser $user): string
    {
        $parts = $user->facts->pluck('body')->all();
        if (mb_strlen(trim((string) $user->description)) >= 20) {
            $parts[] = trim((string) $user->description);
        }

        return implode("\n", $parts);
    }

    /** Автопроверка: вопрос подходит только субъекту и не раскрывает личных данных. */
    private function verify(string $question, BotUser $subject, Collection $distractors): bool
    {
        $labels = ['A', 'B', 'C'];
        $people = collect([$subject])->concat($distractors)->shuffle()->values();
        $block  = $people->map(fn (BotUser $u, int $i) => "{$labels[$i]}:\n" . Str::limit($this->sourceText($u), 700))->implode("\n\n");
        $answer = $labels[$people->search(fn (BotUser $u) => $u->id === $subject->id)];

        $res = $this->ai->json('quest_verify', [
            ['role' => 'system', 'content' => "Ты проверяешь вопрос для игры «угадай, кто это». Даны вопрос и описания трёх людей A, B, C. "
                . "Верни JSON {\"matches\": [буквы людей, к которым вопрос явно подходит по описанию], \"safe\": true|false}. "
                . "safe=false, если вопрос содержит телефон, email, адрес, ссылку, точную дату рождения, имя или фамилию человека, либо оскорбительный или интимный контент."],
            ['role' => 'user', 'content' => "Вопрос: {$question}\n\n{$block}"],
        ]);

        $matches = array_values(array_map('strval', (array) ($res['matches'] ?? [])));

        return ($res['safe'] ?? false) === true && $matches === [$answer];
    }

    private function generatePrompt(): string
    {
        return "Ты придумываешь вопрос для игры знакомств «угадай, кто это». По факту об участнике сформулируй вопрос вида «Кто …?», "
            . "на который можно ответить, выбрав одного из трёх людей. Правила: не называй имён и не цитируй факт дословно, перефразируй; "
            . "не раскрывай контакты, адреса, ссылки, точные даты, не упоминай третьих лиц; тон тёплый и дружелюбный; вопрос до 140 символов. "
            . "Также дай короткое объяснение (до 200 символов), почему это про него, в духе «Он(а) писал(а), что …», без имени. "
            . "Верни только JSON: {\"question\": {\"ru\": \"...\", \"en\": \"...\", \"ro\": \"...\"}, \"explanation\": {\"ru\": \"...\", \"en\": \"...\", \"ro\": \"...\"}}.";
    }

    /** @return array{ru: string, en: string, ro: string}|null */
    private function i18n(mixed $v): ?array
    {
        if (! is_array($v)) {
            return null;
        }
        $out = [];
        foreach (['ru', 'en', 'ro'] as $l) {
            $s = trim((string) ($v[$l] ?? ''));
            if ($s === '' || mb_strlen($s) > 400) {
                return null;
            }
            $out[$l] = $s;
        }

        return $out;
    }

    // ───────────────────────── выдача ─────────────────────────

    /** Следующий неотвеченный квест для игрока или null. */
    public function nextFor(BotUser $user): ?Quest
    {
        $done = QuestAttempt::where('bot_user_id', $user->id)->pluck('quest_id');
        $connected = Connection::where('user_id', $user->id)->pluck('target_id')->all();

        return Quest::active()
            ->where('reports_count', '<', (int) config('game.reports_hide'))
            ->whereNotIn('id', $done)
            ->where('subject_user_id', '!=', $user->id)
            ->when($connected, fn ($q) => $q->whereNotIn('subject_user_id', $connected))
            ->inRandomOrder()
            ->get()
            ->first(fn (Quest $q) => ! in_array($user->id, $q->option_ids, true));
    }

    public function unseenCount(BotUser $user): int
    {
        $done = QuestAttempt::where('bot_user_id', $user->id)->pluck('quest_id');
        $connected = Connection::where('user_id', $user->id)->pluck('target_id')->all();

        return Quest::active()
            ->where('reports_count', '<', (int) config('game.reports_hide'))
            ->whereNotIn('id', $done)
            ->where('subject_user_id', '!=', $user->id)
            ->when($connected, fn ($q) => $q->whereNotIn('subject_user_id', $connected))
            ->get()
            ->filter(fn (Quest $q) => ! in_array($user->id, $q->option_ids, true))
            ->count();
    }

    // ───────────────────────── ответ ─────────────────────────

    /**
     * @return array{correct: bool, correct_user_id: int, explanation: string, gained: int, level_up: int|null, connected: bool, mutual: bool, wait: int}
     */
    public function answer(BotUser $user, Quest $quest, int $chosenId): array
    {
        if (! in_array($chosenId, $quest->option_ids, true)) {
            throw new \InvalidArgumentException('Такого варианта нет.');
        }
        if ($quest->status !== Quest::ACTIVE || in_array($user->id, $quest->option_ids, true) || $quest->subject_user_id === $user->id) {
            throw new \InvalidArgumentException('Этот квест недоступен.');
        }

        $correct = $chosenId === $quest->subject_user_id;

        return DB::transaction(function () use ($user, $quest, $chosenId, $correct) {
            QuestAttempt::create([
                'bot_user_id' => $user->id, 'quest_id' => $quest->id, 'chosen_user_id' => $chosenId, 'correct' => $correct,
            ]);

            $xp = config('game.xp');
            $up = null;
            $connected = false;
            $mutual = false;
            $wait = 0;

            if ($correct) {
                $gained = (int) $xp['quest_correct'];
                $up = $this->game->award($user, 'quest_correct', $gained, (string) $quest->id);

                $conn = Connection::firstOrCreate(
                    ['user_id' => $user->id, 'target_id' => $quest->subject_user_id],
                    ['source' => 'quest'],
                );
                $connected = $conn->wasRecentlyCreated;
                $mutual = $this->settleMutual($user, $quest->subject_user_id, $up);
            } else {
                $gained = (int) $xp['quest_wrong'];
                $up = $this->game->award($user, 'quest_wrong', $gained, (string) $quest->id);
                $wait = (int) config('game.miss_cooldown');
                $user->forceFill(['quest_locked_until' => now()->addSeconds($wait)])->saveQuietly();
            }

            return [
                'correct'         => $correct,
                'correct_user_id' => $quest->subject_user_id,
                'explanation'     => $quest->explanation['ru'] ?? '',
                'gained'          => $gained + ($mutual ? (int) $xp['mutual'] : 0),
                'level_up'        => $up,
                'connected'       => $connected,
                'mutual'          => $mutual,
                'wait'            => $wait,
            ];
        });
    }

    /** Если обе стороны добавили друг друга: нить становится золотой, оба получают очки. */
    private function settleMutual(BotUser $user, int $otherId, ?int &$levelUp): bool
    {
        $other = BotUser::find($otherId);
        $reverse = Connection::where('user_id', $otherId)->where('target_id', $user->id)->first();
        if (! $other || ! $reverse) {
            return false;
        }

        Connection::whereIn('id', Connection::where(function ($q) use ($user, $otherId) {
            $q->where('user_id', $user->id)->where('target_id', $otherId);
        })->orWhere(function ($q) use ($user, $otherId) {
            $q->where('user_id', $otherId)->where('target_id', $user->id);
        })->pluck('id'))->update(['mutual' => true]);

        $pts = (int) config('game.xp.mutual');
        $pair = [min($user->id, $otherId), max($user->id, $otherId)];
        $ref = $pair[0] . '-' . $pair[1];
        $up = $this->game->award($user, 'mutual', $pts, $ref);
        $levelUp = $up ?? $levelUp;
        $this->game->award($other, 'mutual', $pts, $ref);

        return true;
    }

    /** Ответить взаимностью на то, что тебя угадали. */
    public function reciprocate(BotUser $user, BotUser $other): bool
    {
        $incoming = Connection::where('user_id', $other->id)->where('target_id', $user->id)->exists();
        if (! $incoming || $other->id === $user->id) {
            return false;
        }

        Connection::firstOrCreate(['user_id' => $user->id, 'target_id' => $other->id], ['source' => 'reciprocate']);
        $up = null;
        $this->settleMutual($user, $other->id, $up);

        return true;
    }

    public function report(BotUser $user, Quest $quest, ?string $reason): void
    {
        $created = \App\Models\QuestReport::firstOrCreate(
            ['quest_id' => $quest->id, 'bot_user_id' => $user->id],
            ['reason' => $reason ? mb_substr($reason, 0, 200) : null],
        );
        if ($created->wasRecentlyCreated) {
            $quest->increment('reports_count');
            if ($quest->fresh()->reports_count >= (int) config('game.reports_hide')) {
                $quest->update(['status' => Quest::REPORTED]);
            }
        }
    }
}
