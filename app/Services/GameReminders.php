<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\BotUser;
use App\Models\Connection;
use App\Models\LoginToken;
use App\Models\XpEvent;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Напоминания бота об игре. Не чаще одного сообщения на человека за config('game.remind_every_hours').
 * Только настоящие события: новый квест, «тебя узнали», «знакомый получил уровень».
 */
class GameReminders
{
    public function __construct(
        private readonly GameService $game,
        private readonly QuestService $quests,
    ) {}

    /** Разослать напоминания. Возвращает число отправленных. */
    public function run(): int
    {
        if (! $this->game->isLaunched()) {
            return 0;
        }

        $sent  = 0;
        $every = (int) config('game.remind_every_hours');

        BotUser::approved()
            ->where('notifications_muted', false)
            ->where(fn ($q) => $q->whereNull('last_reminded_at')->orWhere('last_reminded_at', '<', now()->subHours($every)))
            ->orderBy('id')
            ->chunkById(100, function ($users) use (&$sent) {
                foreach ($users as $user) {
                    $message = $this->messageFor($user);
                    if ($message === null) {
                        continue;
                    }
                    if ($this->send($user, $message)) {
                        $user->forceFill(['last_reminded_at' => now()])->saveQuietly();
                        $sent++;
                        usleep(50_000); // лимит Telegram ~30 сообщений в секунду
                    }
                }
            });

        return $sent;
    }

    /**
     * Что напомнить этому человеку (по приоритету) или null.
     *
     * @return array{text: string, after: ?\Closure}|null
     */
    public function messageFor(BotUser $user): ?array
    {
        $first = explode(' ', (string) $user->full_name)[0] ?: 'друг';

        // 1. Тебя угадали и ты ещё не ответил(а) взаимностью
        $mine = Connection::where('user_id', $user->id)->pluck('target_id')->all();
        $incoming = Connection::with('user')
            ->where('target_id', $user->id)->whereNull('target_notified_at')
            ->when($mine, fn ($q) => $q->whereNotIn('user_id', $mine))
            ->oldest()->first();
        if ($incoming && $incoming->user) {
            $name = $incoming->user->full_name;

            return [
                'text'  => "{$first}, {$name} узнал(а) тебя в игре «Нити» 👀\nОтветь взаимностью: и ваша нить станет золотой.",
                'after' => fn () => $incoming->update(['target_notified_at' => now()]),
            ];
        }

        // 2. Знакомый из сети получил новый уровень
        $since = $user->last_reminded_at ?? now()->subDays(3);
        $network = Connection::where('user_id', $user->id)->pluck('target_id')
            ->merge(Connection::where('target_id', $user->id)->where('mutual', true)->pluck('user_id'))
            ->unique()->all();
        if ($network) {
            $up = XpEvent::where('type', 'level_up')->whereIn('bot_user_id', $network)->where('created_at', '>=', $since)->latest('id')->first();
            $who = $up ? BotUser::find($up->bot_user_id) : null;
            if ($who) {
                $lvl = $this->game->levelInfo((int) $up->ref)['name'];
                $tail = $user->level < (int) $up->ref ? 'Не отставай 🚀' : 'Загляни в игру, поздравь и найди новых людей.';

                return ['text' => "{$who->full_name} достиг(ла) уровня «{$lvl}» 🎉\n{$tail}", 'after' => null];
            }
        }

        // 3. Новый квест (если человек не заходил больше суток)
        $idle = ! $user->last_active_at || $user->last_active_at->lt(now()->subDay());
        if ($idle && $this->quests->unseenCount($user) > 0) {
            $variants = [
                "{$first}, появился новый квест. Угадаешь, кто это? 🧩",
                "Новая загадка о людях сообщества уже ждёт тебя, {$first}. Заглянешь? ✨",
                "{$first}, новый квест в игре «Нити»: каждый верный ответ добавляет человека в твою сеть 🌐",
                "Появился новый квест. Пройди его, иначе другие участники уйдут вперёд, {$first} ⚡",
            ];

            return ['text' => $variants[array_rand($variants)], 'after' => null];
        }

        return null;
    }

    /** @param array{text: string, after: ?\Closure} $message */
    public function send(BotUser $user, array $message): bool
    {
        $token = LoginToken::generateFor((int) $user->telegram_id);
        $url   = url('/go/' . substr($token->token, 0, 8)) . '?to=game';

        $res = Http::post('https://api.telegram.org/bot' . config('nutgram.token') . '/sendMessage', [
            'chat_id'      => $user->telegram_id,
            'text'         => $message['text'],
            'reply_markup' => json_encode(['inline_keyboard' => [[
                ['text' => '🎮 Открыть игру', 'url' => $url],
                ['text' => '🔕 Тише', 'callback_data' => 'game_mute'],
            ]]]),
        ]);

        if (! $res->successful()) {
            Log::info('Game reminder not delivered', ['telegram_id' => $user->telegram_id, 'status' => $res->status()]);
            // бот заблокирован пользователем: больше не пытаемся
            if ($res->status() === 403) {
                $user->forceFill(['notifications_muted' => true])->saveQuietly();
            }

            return false;
        }

        if ($message['after']) {
            ($message['after'])();
        }

        return true;
    }
}
