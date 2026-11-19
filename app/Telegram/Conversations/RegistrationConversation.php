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
    protected ?string $description = null;   // университет/организация и должность
    protected ?string $phone = null;
    protected ?string $email = null;
    protected ?string $city = null;

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
        $text = trim((string) $bot->message()?->text);

        if (! self::looksLikeFullName($text)) {
            $bot->sendMessage('Напиши, пожалуйста, имя и фамилию текстом, например: Анна Лунгу');
            $this->next('handleName');

            return;
        }

        $this->fullName = $text;

        $bot->sendMessage('Напиши университет/организацию и должность.');

        $this->next('handleDescription');
    }

    /** Имя и фамилия: минимум два слова из букв, без цифр, ссылок и команд бота («/start»). */
    public static function looksLikeFullName(string $text): bool
    {
        $text = trim($text);

        if ($text === '' || mb_strlen($text) < 3 || mb_strlen($text) > 120 || str_starts_with($text, '/')) {
            return false;
        }

        if (preg_match('/[0-9@]|https?:|www\./iu', $text)) {
            return false;
        }

        $words = preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $words = array_filter($words, static fn (string $w): bool => preg_match('/\p{L}{2,}/u', $w) === 1);

        return count($words) >= 2;
    }

    public function handleDescription(Nutgram $bot): void
    {
        $text = trim((string) $bot->message()?->text);

        if ($text === '' || str_starts_with($text, '/')) {
            $bot->sendMessage('Пожалуйста, напиши университет/организацию и должность текстом.');
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

        $bot->sendMessage('Напиши с какого ты города');

        $this->next('handleCity');
    }

    public function handleCity(Nutgram $bot): void
    {
        $text = trim((string) $bot->message()?->text);

        if ($text === '' || str_starts_with($text, '/') || mb_strlen($text) > 80 || preg_match('/\p{L}/u', $text) !== 1) {
            $bot->sendMessage('Напиши, пожалуйста, название города текстом, например: Кишинёв');
            $this->next('handleCity');

            return;
        }

        $this->city = $text;

        $bot->sendMessage(
            text: "Согласие на обработку персональных данных\n\nЯ даю согласие на обработку предоставленных мной персональных данных (имя, название компании, телефон, электронная почта) в целях регистрации, обратной связи и информирования по вопросам участия в программах INSPIRE EDU.",
            reply_markup: InlineKeyboardMarkup::make()
                ->addRow(InlineKeyboardButton::make('✅ Да', callback_data: 'reg:consent:yes')),
        );

        $this->next('handleConsent');
    }

    public function handleConsent(Nutgram $bot): void
    {
        $data = $bot->callbackQuery()?->data;

        if ($data === 'reg:consent:yes') {
            // Сразу отвечаем на нажатие и убираем кнопки: человек видит, что выбор принят, пока идёт сохранение
            $bot->answerCallbackQuery(text: 'Принято ✅');
            try {
                $bot->editMessageReplyMarkup(reply_markup: InlineKeyboardMarkup::make());
            } catch (\Throwable) {
                // не критично
            }

            try {
                $this->save($bot);
            } catch (\Throwable $e) {
                logger()->error('Registration failed', ['telegram_id' => $bot->userId(), 'error' => $e->getMessage()]);
                $bot->sendMessage('Не получилось завершить регистрацию. Нажми /start и попробуй ещё раз. Если не поможет, напиши нам.');
                $this->end();
            }

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
            'city'              => $this->city,
            'contact_phone'     => $this->phone,
            'contact_email'     => $this->email,
            'consent_at'        => now(),
            'status'            => BotUser::STATUS_APPROVED,
            'approved_at'       => now(),
        ]);

        // Compute embedding asynchronously — non-blocking
        ComputeUserEmbedding::dispatch($botUser);

        $firstName = explode(' ', (string) $this->fullName)[0];

        // Сначала ответ человеку, всё остальное (фото из Telegram) после: это самые медленные вызовы
        $bot->sendMessage(
            "Привет, {$firstName}! 👋\n\nТы теперь в сообществе INSPIRE EDU Network.\n\nБот будет присылать тебе новости и возможности, которые публикуются на нашем сайте: курсы, программы и поездки за рубежом.\n\nВ личном кабинете тебя ждут участники сообщества, возможности и интерактивные игры.\nКабинет открывается кнопкой «Открыть кабинет» или кнопкой «Кабинет» рядом с полем ввода сообщения.",
            reply_markup: \App\Telegram\CabinetButton::keyboard(),
        );

        $this->end();

        \App\Jobs\FetchTelegramAvatar::dispatch($botUser);
    }
}
