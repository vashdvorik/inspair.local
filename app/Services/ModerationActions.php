<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\BotUser;
use App\Models\ModerationFlag;

/** Решения администратора по предупреждениям нейросети: одинаково работают в админке и по кнопкам в Telegram. */
class ModerationActions
{
    /** Теневой бан участника; его открытые предупреждения закрываются. */
    public static function ban(BotUser $user, ?string $note = null): void
    {
        $user->update(['status' => BotUser::STATUS_BANNED, 'moderation_note' => $note ?: $user->moderation_note]);
        ModerationFlag::where('bot_user_id', $user->id)->where('status', ModerationFlag::OPEN)->update(['status' => ModerationFlag::BANNED, 'reviewed_at' => now()]);
    }

    /** «Нарушения нет»: все открытые предупреждения участника закрываются, эти же тексты больше не поднимаются. */
    public static function dismiss(BotUser $user): int
    {
        return ModerationFlag::where('bot_user_id', $user->id)->where('status', ModerationFlag::OPEN)->update(['status' => ModerationFlag::DISMISSED, 'reviewed_at' => now()]);
    }
}
