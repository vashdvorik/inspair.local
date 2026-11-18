<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\BotUser;
use App\Services\ModerationNotifier;
use App\Services\ProfileModerator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Нейросеть проверяет тексты участников, которые появились или изменились с прошлой проверки, и создаёт
 * предупреждения для администратора (раздел «Модерация»). Запускается по расписанию каждые 5 минут.
 *
 *     php artisan moderation:scan            # новые и изменённые тексты (не больше 30 участников за раз)
 *     php artisan moderation:scan --all      # перепроверить всех, даже если тексты не менялись
 */
class ScanProfiles extends Command
{
    protected $signature = 'moderation:scan {--limit=30 : сколько участников проверить за запуск} {--all : проверить всех заново}';

    protected $description = 'Нейросеть проверяет тексты участников и отмечает подозрительное для администратора';

    public function handle(ProfileModerator $moderator, ModerationNotifier $notifier): int
    {
        $limit = max(1, (int) $this->option('limit'));
        $checked = $flags = 0;

        // забаненные уже скрыты от других, тратить на них проверки не нужно
        BotUser::approved()->with('facts')->orderBy('id')->chunkById(100, function ($users) use ($moderator, $notifier, $limit, &$checked, &$flags): bool {
            foreach ($users as $user) {
                if ($checked >= $limit) {
                    return false;
                }
                if (! $this->option('all') && ! $moderator->needsCheck($user)) {
                    continue;
                }

                try {
                    $new = $moderator->check($user);
                    $checked++;
                    $flags += count($new);
                    $notifier->notify($user, $new);
                } catch (\Throwable $e) {
                    // нет ключа, лимит или сбой сети: дальше пробовать бессмысленно, повторим в следующий запуск
                    Log::info('Модерация текстов: остановлена', ['error' => $e->getMessage()]);
                    $this->warn('Остановлено: '.$e->getMessage());

                    return false;
                }
            }

            return true;
        });

        $this->info("Проверено участников: {$checked}, новых предупреждений: {$flags}");

        return self::SUCCESS;
    }
}
