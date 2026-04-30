<?php

declare(strict_types=1);

namespace App\Telegram;

use SergiX44\Nutgram\Telegram\Types\Keyboard\KeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardMarkup;

class TelegramKeyboards
{
    public const BTN_CABINET  = '📋 Войти в кабинет';
    public const BTN_MATCHES  = '🤝 Матчи';
    public const BTN_CHAT     = '💬 Чат';
    public const BTN_CARD     = '🗂️ Моя визитка';

    public static function mainMenu(): ReplyKeyboardMarkup
    {
        return ReplyKeyboardMarkup::make(resize_keyboard: true)
            ->addRow(
                KeyboardButton::make(self::BTN_MATCHES),
                KeyboardButton::make(self::BTN_CHAT),
            )
            ->addRow(
                KeyboardButton::make(self::BTN_CARD),
            );
    }
}
