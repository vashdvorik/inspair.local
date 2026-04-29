<?php

declare(strict_types=1);

/** @var SergiX44\Nutgram\Nutgram $bot */

use App\Models\BotUser;
use App\Telegram\Conversations\RegistrationConversation;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\KeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardMarkup;

/*
|--------------------------------------------------------------------------
| Nutgram Telegram Bot Handlers
|--------------------------------------------------------------------------
*/

$bot->onCommand('start', function (Nutgram $bot) {
    $telegramId = $bot->userId();
    $user = BotUser::where('telegram_id', $telegramId)->first();

    if ($user === null) {
        RegistrationConversation::begin($bot);
        return;
    }

    if ($user->isPending()) {
        $firstName = explode(' ', (string) $user->full_name)[0];
        $bot->sendMessage(
            "{$firstName}, твоя заявка уже в работе 🙌\n\nАдминистратор лично рассматривает и свяжется в течение 24 часов.\nЕсли есть срочный вопрос — @lesnichenkoP"
        );
        return;
    }

    if ($user->isApproved()) {
        $bot->sendMessage(
            text: 'Добро пожаловать! Используй меню ниже 👇',
            reply_markup: mainMenuKeyboard(),
        );
        return;
    }

    $bot->sendMessage('🔒 Ваш доступ был закрыт.' . "\n\n" . 'Если у тебя есть вопросы или ты хочешь узнать причину — напиши напрямую: @lesnichenkoP');
})->description('Запустить бота');

// Fallback: обрабатывает все сообщения, не попавшие в другие обработчики
$bot->fallback(function (Nutgram $bot) {
    $telegramId = $bot->userId();
    $user = BotUser::where('telegram_id', $telegramId)->first();

    if ($user === null) {
        RegistrationConversation::begin($bot);
        return;
    }

    if ($user->isPending()) {
        $firstName = explode(' ', (string) $user->full_name)[0];
        $bot->sendMessage(
            "{$firstName}, твоя заявка уже в работе 🙌\n\nАдминистратор лично рассматривает и свяжется в течение 24 часов.\nЕсли есть срочный вопрос — @lesnichenkoP"
        );
        return;
    }

    if ($user->isApproved()) {
        $text = $bot->message()?->text;
        match ($text) {
            '📋 Моя визитка' => $bot->sendMessage("Раздел в разработке 🚧"),
            '🤝 Матчи'       => $bot->sendMessage("Раздел в разработке 🚧"),
            '💬 Чат'         => $bot->sendMessage("Раздел в разработке 🚧"),
            '🗂️ Кабинет'     => $bot->sendMessage("Раздел в разработке 🚧"),
            default          => null,
        };
        return;
    }

    // Отклонённый пользователь
    $bot->sendMessage('🔒 Ваш доступ был закрыт.' . "\n\n" . 'Если у тебя есть вопросы или ты хочешь узнать причину — напиши напрямую: @lesnichenkoP');
});

/**
 * Постоянное меню для одобренных участников.
 */
function mainMenuKeyboard(): ReplyKeyboardMarkup
{
    return ReplyKeyboardMarkup::make(resize_keyboard: true)
        ->addRow(
            KeyboardButton::make('📋 Моя визитка'),
            KeyboardButton::make('🤝 Матчи'),
        )
        ->addRow(
            KeyboardButton::make('💬 Чат'),
            KeyboardButton::make('🗂️ Кабинет'),
        );
}
