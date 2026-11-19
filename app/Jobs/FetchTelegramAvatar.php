<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\BotUser;
use App\Services\TelegramAvatar;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;

/** Фото профиля из Telegram подтягивается в очереди: при массовой регистрации бот не тратит на это время ответа. */
class FetchTelegramAvatar implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public int $tries   = 2;
    public int $timeout = 30;

    public function __construct(public readonly BotUser $user) {}

    public function handle(TelegramAvatar $avatar): void
    {
        $avatar->fetch($this->user); // сервис сам ничего не бросает
    }
}
