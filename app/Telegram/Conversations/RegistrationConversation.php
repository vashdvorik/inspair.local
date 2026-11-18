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
    protected ?string $description = null;

    public function start(Nutgram $bot): void
    {
        $firstName = $bot->user()?->first_name ?? '';

        $bot->sendMessage(
            text: "Привет! 👋\n\nРад что ты здесь — это первый шаг к вступлению в Инспайр сообщество.\n\nИнспайр — сообщество для молодых людей, которые ищут возможности для роста, партнерства и интересные мероприятия.\n\nЗадам несколько коротких вопросов — займёт 1 минуту.\n\nГотов?",
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
            text: "Кто ты и чем занимаешься?\n\nРоль, сфера, компания — в 2-3 предложениях.",
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

        $this->save($bot);
    }

    private function save(Nutgram $bot): void
    {
        $telegramUser = $bot->user();

        $botUser = BotUser::create([
            'telegram_id'       => $telegramUser->id,
            'telegram_username' => $telegramUser->username,
            'first_name'        => $telegramUser->first_name,
            'full_name'         => $this->fullName,
            'description'       => $this->description,
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
