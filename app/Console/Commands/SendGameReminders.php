<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\GameReminders;
use Illuminate\Console\Command;

class SendGameReminders extends Command
{
    protected $signature = 'game:remind';

    protected $description = 'Разослать напоминания об игре в Telegram (не чаще раза в сутки на человека)';

    public function handle(GameReminders $reminders): int
    {
        $this->info('Отправлено напоминаний: ' . $reminders->run());

        return self::SUCCESS;
    }
}
