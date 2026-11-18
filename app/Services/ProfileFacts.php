<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\BotUser;
use App\Models\ProfileFact;

/** Короткие факты о себе: из них ИИ составляет вопросы игры. Правятся и в игре, и в профиле. */
class ProfileFacts
{
    /**
     * Существующие факты правятся по id (очищенный удаляется), новые добавляются до лимита.
     *
     * @param  array<int|string, mixed>  $existing
     * @param  array<int, mixed>  $added
     */
    public function sync(BotUser $user, array $existing, array $added): void
    {
        $max = (int) config('game.max_facts');

        foreach ($user->facts as $fact) {
            if (! array_key_exists($fact->id, $existing)) {
                continue;
            }
            $body = trim((string) $existing[$fact->id]);
            if ($body === '') {
                $fact->delete();
            } elseif ($body !== $fact->body) {
                $fact->update(['body' => $body]);
            }
        }

        $count = $user->facts()->count();
        foreach ($added as $body) {
            $body = trim((string) $body);
            if ($body === '' || $count >= $max) {
                continue;
            }
            ProfileFact::create(['bot_user_id' => $user->id, 'body' => mb_substr($body, 0, 300)]);
            $count++;
        }
    }
}
