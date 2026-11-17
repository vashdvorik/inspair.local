<?php

declare(strict_types=1);

namespace App\Telegram;

use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardRemove;
use SergiX44\Nutgram\Telegram\Types\WebApp\WebAppInfo;

/**
 * Кнопка под сообщением бота, которая открывает кабинет как Mini App внутри Telegram (тип web_app, не обычная ссылка:
 * ссылка открылась бы в браузере). Вход в кабинете происходит сам по данным Telegram.
 */
final class CabinetButton
{
    public static function url(): string
    {
        return url('/app/account/login');
    }

    public static function button(): InlineKeyboardButton
    {
        return InlineKeyboardButton::make('📋 Открыть кабинет', web_app: WebAppInfo::make(self::url()));
    }

    /** Клавиатура из одной кнопки; дополнительные кнопки идут отдельными рядами. */
    public static function keyboard(InlineKeyboardButton ...$more): InlineKeyboardMarkup
    {
        $markup = InlineKeyboardMarkup::make()->addRow(self::button());
        foreach ($more as $button) {
            $markup->addRow($button);
        }

        return $markup;
    }

    /**
     * Убирает старую клавиатуру («Матчи», «Чат»), если она ещё висит у участника. Сообщение с командой
     * «убрать клавиатуру» нельзя совместить с кнопкой под текстом, поэтому оно отправляется отдельно и тут же удаляется.
     */
    public static function removeOldKeyboard(Nutgram $bot, int|string $chatId): void
    {
        try {
            $message = $bot->sendMessage('…', chat_id: $chatId, reply_markup: ReplyKeyboardRemove::make(true));
            if ($message) {
                $bot->deleteMessage($chatId, $message->message_id);
            }
        } catch (\Throwable) {
            // не критично: клавиатура просто останется до следующего сообщения
        }
    }
}
