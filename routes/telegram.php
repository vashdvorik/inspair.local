<?php

declare(strict_types=1);

/** @var SergiX44\Nutgram\Nutgram $bot */

use App\Models\BotUser;
use App\Telegram\Conversations\RegistrationConversation;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

/*
|--------------------------------------------------------------------------
| Nutgram Telegram Bot Handlers
|--------------------------------------------------------------------------
*/

// Inline button: "С чего начать?" — инструкция для новых участников
$bot->onCallbackQueryData('start_guide', function (Nutgram $bot) {
    $bot->answerCallbackQuery();

    $bot->sendMessage(
        "🚀 Как начать работу в Инспайр?\n\n"
        . "1️⃣ Открой кабинет\n"
        . "Нажми кнопку «Кабинет» рядом с полем ввода сообщения: откроется личный кабинет участника.\n\n"
        . "2️⃣ Заполни профиль\n"
        . "Укажи своё имя, чем занимаешься и чем можешь быть полезен сообществу. Чем подробнее, тем интереснее игра.\n\n"
        . "3️⃣ Играй и знакомься\n"
        . "В кабинете доступны список участников, возможности и игра «Угадай, кто это».\n\n"
        . "💡 Всё просто: открыл кабинет → заполнил профиль → играешь и знакомишься."
    );
});

$bot->onCommand('start', function (Nutgram $bot) {
    $user = BotUser::where('telegram_id', $bot->userId())->first();

    if ($user === null) {
        RegistrationConversation::begin($bot);
        return;
    }

    \App\Telegram\CabinetButton::removeOldKeyboard($bot, $bot->chatId());
    sendCabinetHint($bot, $user);
})->description('Запустить бота');

// Любое другое сообщение: незарегистрированного ведём в регистрацию, остальным напоминаем, как открыть кабинет
$bot->fallback(function (Nutgram $bot) {
    $user = BotUser::where('telegram_id', $bot->userId())->first();

    if ($user === null) {
        RegistrationConversation::begin($bot);
        return;
    }

    sendCabinetHint($bot, $user);
});

/**
 * Бот больше не присылает ссылку для входа: кабинет открывается только кнопкой меню «Кабинет» (мини-приложение).
 * Заодно убираем старую клавиатуру («Матчи», «Чат»), которая могла остаться у участника.
 */
function sendCabinetHint(Nutgram $bot, BotUser $user): void
{
    $firstName = explode(' ', (string) $user->full_name)[0];
    $bot->sendMessage(
        "Привет, {$firstName}! Личный кабинет открывается по кнопке ниже или по кнопке «Кабинет» рядом с полем ввода сообщения.",
        reply_markup: \App\Telegram\CabinetButton::keyboard()
    );
}
