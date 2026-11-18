<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Broadcast;
use Illuminate\Support\Facades\Storage;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;
use SergiX44\Nutgram\Telegram\Types\Internal\InputFile;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\WebApp\WebAppInfo;

/** Отправляет сообщение администратора одному участнику: текст (Telegram HTML), по желанию картинка и кнопки. */
class BroadcastMessenger
{
    private const CAPTION_LIMIT = 1024;

    /** @throws \Throwable если Telegram отказал (бот заблокирован, неверная разметка и т. п.) */
    public function send(int $chatId, Broadcast $b): void
    {
        $bot = app(Nutgram::class);
        $markup = $this->markup($b);
        $text = $b->body;
        $image = $b->image_path && Storage::disk('public')->exists($b->image_path) ? Storage::disk('public')->path($b->image_path) : null;

        if ($image === null) {
            $bot->sendMessage(text: $text, chat_id: $chatId, parse_mode: ParseMode::HTML, reply_markup: $markup);

            return;
        }

        $photo = fn () => InputFile::make(fopen($image, 'r'), basename($image));

        // подпись к фото ограничена 1024 символами: длинный текст идёт отдельным сообщением под картинкой
        if (mb_strlen(strip_tags($text)) <= self::CAPTION_LIMIT) {
            $bot->sendPhoto(photo: $photo(), chat_id: $chatId, caption: $text, parse_mode: ParseMode::HTML, reply_markup: $markup);

            return;
        }

        $bot->sendPhoto(photo: $photo(), chat_id: $chatId);
        $bot->sendMessage(text: $text, chat_id: $chatId, parse_mode: ParseMode::HTML, reply_markup: $markup);
    }

    private function markup(Broadcast $b): ?InlineKeyboardMarkup
    {
        $keyboard = $b->keyboard();
        if ($keyboard === []) {
            return null;
        }

        $markup = InlineKeyboardMarkup::make();
        foreach ($keyboard as $k) {
            $markup->addRow($k['web_app']
                ? InlineKeyboardButton::make($k['label'], web_app: WebAppInfo::make($k['url']))
                : InlineKeyboardButton::make($k['label'], url: $k['url']));
        }

        return $markup;
    }
}
