<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\BotUser;
use App\Models\ModerationFlag;
use Illuminate\Support\Facades\Log;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

/** Сообщает администраторам в Telegram, что нейросеть нашла подозрительное: с кнопками «Скрыть участника» и «Нарушения нет». */
class ModerationNotifier
{
    /** Telegram ID всех, кому приходят предупреждения. */
    public static function adminIds(): array
    {
        $fromBot = BotUser::where('is_moderator', true)->whereNotNull('telegram_id')->pluck('telegram_id')->map(fn ($id) => (int) $id)->all();
        $fromEnv = array_map('intval', (array) config('moderation.admin_telegram_ids'));

        return array_values(array_unique(array_merge($fromBot, $fromEnv)));
    }

    public static function isAdmin(int $telegramId): bool
    {
        return in_array($telegramId, self::adminIds(), true);
    }

    /** @param list<ModerationFlag> $flags новые предупреждения одного участника */
    public function notify(BotUser $user, array $flags): void
    {
        if ($flags === []) {
            return;
        }

        $text = $this->text($user, $flags);
        $markup = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('🙈 Скрыть участника', callback_data: "mod:ban:{$user->id}"),
                InlineKeyboardButton::make('✅ Нарушения нет', callback_data: "mod:ok:{$user->id}"),
            )
            ->addRow(InlineKeyboardButton::make('Открыть в админке', url: rtrim((string) config('app.url'), '/').'/admin/moderation-flags'));

        foreach (self::adminIds() as $chatId) {
            try {
                $this->deliver($chatId, $text, $markup);
            } catch (\Throwable $e) {
                Log::info('Модерация: не удалось отправить предупреждение администратору', ['telegram_id' => $chatId, 'error' => $e->getMessage()]);
            }
        }
    }

    /** @param list<ModerationFlag> $flags */
    public function text(BotUser $user, array $flags): string
    {
        $who = trim(($user->full_name ?: 'без имени').($user->telegram_username ? ' (@'.$user->telegram_username.')' : ''));
        $lines = ['⚠️ Модерация: нейросеть нашла подозрительное', '', 'Участник: '.$who];

        foreach ($flags as $f) {
            $severity = match ($f->severity) { 'high' => 'серьёзно', 'medium' => 'похоже', default => 'возможно' };
            $lines[] = '';
            $lines[] = (ModerationFlag::FIELDS[$f->field] ?? $f->field).': '.(ModerationFlag::CATEGORIES[$f->category] ?? $f->category).' ('.$severity.')';
            if ($f->reason) {
                $lines[] = $f->reason;
            }
            $lines[] = '«'.mb_substr($f->excerpt, 0, 300).(mb_strlen($f->excerpt) > 300 ? '…' : '').'»';
        }

        return implode("\n", $lines);
    }

    protected function deliver(int $chatId, string $text, InlineKeyboardMarkup $markup): void
    {
        app(Nutgram::class)->sendMessage(text: $text, chat_id: $chatId, reply_markup: $markup);
    }
}
