<?php

declare(strict_types=1);

namespace App\Telegram\Conversations;

use App\Models\BotUser;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

class RegistrationConversation extends Conversation
{
    protected ?string $fullName = null;
    protected ?string $description = null;
    protected ?string $expectation = null;

    public function start(Nutgram $bot): void
    {
        $firstName = $bot->user()?->first_name ?? '';

        $bot->sendMessage(
            text: "Привет! 👋\n\nРад что ты здесь — это первый шаг к вступлению в Инспайр сообщество.\n\nИнспайр — сообщество для молодых людей, которые ищут вдохновляющее сообщество, партнерства и интересные мероприятия.\n\nЗадам несколько коротких вопросов — займёт 2-3 минуты.\n\nГотов?",
            reply_markup: InlineKeyboardMarkup::make()
                ->addRow(InlineKeyboardButton::make('Да', callback_data: 'reg:yes')),
        );

        $this->next('waitForConfirmation');
    }

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

        $bot->sendMessage(
            text: "Кто ты и чем занимаешься?\n\nРоль, сфера, компания — в 2-3 предложениях.\nСайт или соцсеть — приложи ссылкой если есть.",
        );

        $this->next('handleDescription');
    }

    public function handleDescription(Nutgram $bot): void
    {
        $text = $bot->message()?->text;

        if (empty($text)) {
            $bot->sendMessage('Пожалуйста, расскажи о себе текстом.');
            $this->next('handleDescription');

            return;
        }

        $this->description = $text;

        $bot->sendMessage(
            text: "Что ждёшь от Инспайр и чем можешь быть полезен сообществу?",
            reply_markup: InlineKeyboardMarkup::make()
                ->addRow(InlineKeyboardButton::make('Пропустить', callback_data: 'reg:skip')),
        );

        $this->next('handleExpectation');
    }

    public function handleExpectation(Nutgram $bot): void
    {
        if ($bot->callbackQuery()?->data === 'reg:skip') {
            $bot->answerCallbackQuery();
            $this->expectation = null;
        } else {
            $text = $bot->message()?->text;
            if (empty($text)) {
                $this->next('handleExpectation');

                return;
            }
            $this->expectation = $text;
        }

        $this->save($bot);
    }

    private function save(Nutgram $bot): void
    {
        $telegramUser = $bot->user();

        BotUser::create([
            'telegram_id'       => $telegramUser->id,
            'telegram_username' => $telegramUser->username,
            'first_name'        => $telegramUser->first_name,
            'full_name'         => $this->fullName,
            'description'       => $this->description,
            'expectation'       => $this->expectation,
            'status'            => BotUser::STATUS_PENDING,
        ]);

        $firstName = explode(' ', (string) $this->fullName)[0];

        $bot->sendMessage(
            "Спасибо, {$firstName}!\n\nЗаявка принята. Мы рассмотрим её и свяжемся с тобой в Telegram в течение 24 часов.\n\nПока можешь посмотреть сайт сообщества:\n".config('nutgram.community_url', 'https://inspair.community'),
        );

        $this->end();
    }
}
