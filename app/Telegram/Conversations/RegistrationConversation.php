<?php

declare(strict_types=1);

namespace App\Telegram\Conversations;

use App\Jobs\ComputeUserEmbedding;
use App\Models\BotUser;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

class RegistrationConversation extends Conversation
{
    protected ?string $fullName = null;
    protected ?string $description = null;   // университет или организация
    protected ?string $phone = null;
    protected ?string $email = null;

    public function start(Nutgram $bot): void
    {
        $bot->sendMessage("Привет! 👋\n\nРад что ты здесь — это первый шаг к вступлению в INSPIRE EDU Network.\n\nЗадам несколько коротких вопросов — займёт 1 минуту.\n\nКак тебя зовут? (имя и фамилия)");

        $this->next('handleName');
    }

    /** Осталось для тех, кто начал регистрацию со старой кнопкой «Да»: новым участникам этот шаг не показывается. */
    public function waitForConfirmation(Nutgram $bot): void
    {
        if ($bot->callbackQuery()?->data === 'reg:yes') {
            $bot->answerCallbackQuery();

            $bot->sendMessage('Как тебя зовут? (имя и фамилия)');
            $this->next('handleName');

            return;
        }

        // Пользователь написал что-то до нажатия кнопки — повторяем вопрос
        $this->next('waitForConfirmation');
    }

    public function handleName(Nutgram $bot): void
    {
        $text = $bot->message()?->text;

        if (empty($text)) {
            $bot->sendMessage('Пожалуйста, введи имя и фамилию текстом.');
            $this->next('handleName');

            return;
        }

        $this->fullName = $text;

        $bot->sendMessage('Напиши университет или организацию, которую ты представляешь.');

        $this->next('handleDescription');
    }

    public function handleDescription(Nutgram $bot): void
    {
        $text = trim((string) $bot->message()?->text);

        if ($text === '') {
            $bot->sendMessage('Пожалуйста, напиши название университета или организации текстом.');
            $this->next('handleDescription');

            return;
        }

        $this->description = $text;

        $bot->sendMessage('Твой телефон');

        $this->next('handlePhone');
    }

    public function handlePhone(Nutgram $bot): void
    {
        $text = trim((string) $bot->message()?->text);

        if (! preg_match('/^\+?[0-9][0-9\s().-]{5,28}$/', $text)) {
            $bot->sendMessage('Не похоже на номер телефона. Напиши, например: +373 600 00000');
            $this->next('handlePhone');

            return;
        }

        $this->phone = $text;

        $bot->sendMessage('Твой email');

        $this->next('handleEmail');
    }

    public function handleEmail(Nutgram $bot): void
    {
        $text = trim((string) $bot->message()?->text);

        if ($text === '' || mb_strlen($text) > 120 || filter_var($text, FILTER_VALIDATE_EMAIL) === false) {
            $bot->sendMessage('Не похоже на email. Напиши, например: name@example.com');
            $this->next('handleEmail');

            return;
        }

        $this->email = $text;

        $bot->sendMessage(
            text: "Согласие на обработку персональных данных\n\nЯ даю согласие на обработку предоставленных мной персональных данных (имя, название компании, телефон, электронная почта) в целях регистрации, обратной связи и информирования по вопросам участия в программах INSPIRE EDU.",
            reply_markup: InlineKeyboardMarkup::make()
                ->addRow(
                    InlineKeyboardButton::make('✅ Согласен(на)', callback_data: 'reg:consent:yes'),
                    InlineKeyboardButton::make('❌ Не согласен(на)', callback_data: 'reg:consent:no'),
                ),
        );

        $this->next('handleConsent');
    }

    public function handleConsent(Nutgram $bot): void
    {
        $data = $bot->callbackQuery()?->data;

        if ($data === 'reg:consent:yes') {
            $bot->answerCallbackQuery();
            $this->save($bot);

            return;
        }

        if ($data === 'reg:consent:no') {
            $bot->answerCallbackQuery();
            $bot->sendMessage('Без согласия на обработку персональных данных мы не можем завершить регистрацию. Если передумаешь, нажми /start.');
            $this->end();

            return;
        }

        // Написали текст вместо нажатия кнопки: ждём выбор
        $this->next('handleConsent');
    }

    private function save(Nutgram $bot): void
    {
        $telegramUser = $bot->user();

        $botUser = BotUser::create([
            'telegram_id'       => $telegramUser->id,
            'telegram_username' => $telegramUser->username,
            'first_name'        => $telegramUser->first_name,
            'full_name'         => $this->fullName,
            'organization'      => $this->description,
            'contact_phone'     => $this->phone,
            'contact_email'     => $this->email,
            'consent_at'        => now(),
            'status'            => BotUser::STATUS_APPROVED,
            'approved_at'       => now(),
        ]);

        $this->downloadAvatar($bot, $botUser);

        // Compute embedding asynchronously — non-blocking
        ComputeUserEmbedding::dispatch($botUser);

        $firstName = explode(' ', (string) $this->fullName)[0];

        \App\Telegram\CabinetButton::removeOldKeyboard($bot, $botUser->telegram_id);

        $bot->sendMessage(
            "Привет, {$firstName}! 👋\n\nТы теперь в сообществе INSPIRE EDU Network.\n\nБот будет присылать тебе новости и возможности, которые публикуются на нашем сайте: курсы, программы и поездки за рубежом.\n\nВ личном кабинете тебя ждут участники сообщества, возможности и интерактивные игры.\nКабинет открывается кнопкой «Открыть кабинет» или кнопкой «Кабинет» рядом с полем ввода сообщения.",
            reply_markup: \App\Telegram\CabinetButton::keyboard(),
        );

        $this->end();
    }

    private function downloadAvatar(Nutgram $bot, BotUser $botUser): void
    {
        // Не блокируем регистрацию из-за ошибки загрузки фото; сервис сам ничего не бросает
        app(\App\Services\TelegramAvatar::class)->fetch($botUser);
    }
}
