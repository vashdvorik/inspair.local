<?php

declare(strict_types=1);

/** @var SergiX44\Nutgram\Nutgram $bot */

use App\Models\BotUser;
use App\Models\LoginToken;
use App\Telegram\Conversations\RegistrationConversation;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\Keyboard\KeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardMarkup;

/*
|--------------------------------------------------------------------------
| Nutgram Telegram Bot Handlers
|--------------------------------------------------------------------------
*/

$bot->onCommand('start', function (Nutgram $bot) {    $telegramId = $bot->userId();
    $user = BotUser::where('telegram_id', $telegramId)->first();

    // ?start=login deep-link → bot receives "/start login" or "/start@botname login"
    preg_match('/^\/start(?:@\w+)?\s*(.*)/i', $bot->message()?->text ?? '', $m);
    $startParam = trim($m[1] ?? '');

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
        // ?start=login deep-link OR any /start → send login link immediately
        if ($startParam === 'login' || $startParam === '') {
            sendLoginLink($bot, $user);
            return;
        }

        $bot->sendMessage(
            text: 'Добро пожаловать! Используй меню ниже 👇',
            reply_markup: mainMenuKeyboard(),
        );
        return;
    }

    $bot->sendMessage('🔒 Ваш доступ был закрыт.' . "\n\n" . 'Если у тебя есть вопросы или ты хочешь узнать причину — напиши напрямую: @lesnichenkoP');
})->description('Запустить бота');

// /login — send magic link to approved users
$bot->onCommand('login', function (Nutgram $bot) {
    $telegramId = $bot->userId();
    $user = BotUser::where('telegram_id', $telegramId)->first();

    if (! $user || ! $user->isApproved()) {
        $bot->sendMessage('🔒 Вход доступен только одобренным участникам сообщества.');
        return;
    }

    sendLoginLink($bot, $user);
})->description('Войти в личный кабинет');

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
            '🗂️ Кабинет'     => sendLoginLink($bot, $user),
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

/**
 * Generate a magic login link and send it to the user.
 */
function sendLoginLink(Nutgram $bot, BotUser $user): void
{
    $token = LoginToken::generateFor((int) $user->telegram_id);
    $url   = url('/go/' . substr($token->token, 0, 8));

    $firstName = explode(' ', (string) $user->full_name)[0];

    $keyboard = InlineKeyboardMarkup::make()
        ->addRow(
            InlineKeyboardButton::make('🔐 Войти в кабинет →', url: $url)
        );

    $bot->sendMessage(
        "Привет, {$firstName}! Нажми кнопку ниже, чтобы войти в личный кабинет.\n\n⏱ Ссылка действует 1 час и работает один раз.",
        reply_markup: $keyboard
    );
}
