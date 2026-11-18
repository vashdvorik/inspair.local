<?php

declare(strict_types=1);

namespace App\Services;

use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\WebApp\WebAppInfo;

/** Отправка одного сообщения о возможности участнику: текст и кнопка, которая открывает страницу в кабинете (мини-приложение). */
class OpportunityMessenger
{
    /** @throws \Throwable если Telegram отказал (например, участник заблокировал бота) */
    public function send(int $chatId, string $text, string $buttonUrl, string $buttonLabel): void
    {
        app(Nutgram::class)->sendMessage(
            text: $text,
            chat_id: $chatId,
            reply_markup: InlineKeyboardMarkup::make()->addRow(InlineKeyboardButton::make($buttonLabel, web_app: WebAppInfo::make($buttonUrl))),
        );
    }
}
