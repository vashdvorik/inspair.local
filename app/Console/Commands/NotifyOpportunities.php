<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\BotUser;
use App\Services\OpportunityMessenger;
use App\Support\OpportunityFeed;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Рассылка новых возможностей участникам в бот.
 *
 * Берёт возможности из resources/data/opportunities.json, находит те, о которых бот ещё не писал, и отправляет каждую
 * всем, кто не отключил уведомления в профиле. Первый запуск ничего не рассылает: все возможности, которые уже есть
 * на сайте, помечаются как «уже были», чтобы участники не получили сразу пачку старых новостей (с --all отправит всё).
 * Возможности с прошедшим сроком подачи заявок не рассылаются.
 *
 *     php artisan opportunities:notify            # обычный запуск (по расписанию каждые 15 минут)
 *     php artisan opportunities:notify --dry      # показать, что и кому ушло бы, ничего не отправляя
 */
class NotifyOpportunities extends Command
{
    protected $signature = 'opportunities:notify {--all : отправить и те возможности, которые уже были при первом запуске} {--dry : только показать, ничего не отправлять}';

    protected $description = 'Рассылает новые возможности участникам в Telegram-бот (каждая уходит один раз)';

    public function handle(OpportunityMessenger $messenger): int
    {
        $items = OpportunityFeed::all();
        $sent = DB::table('opportunity_broadcasts')->pluck('slug')->all();
        $dry = (bool) $this->option('dry');

        if ($sent === [] && ! $this->option('all')) {
            if (! $dry) {
                foreach ($items as $o) {
                    DB::table('opportunity_broadcasts')->insert(['slug' => $o['slug'], 'baseline' => true, 'created_at' => now(), 'updated_at' => now()]);
                }
            }
            $this->info('Первый запуск: '.count($items).' возможностей, которые уже есть на сайте, отмечены как отправленные. Рассылаться будут только новые.');

            return self::SUCCESS;
        }

        // старые раньше новых: участники получают их в порядке появления
        $new = array_reverse(array_values(array_filter($items, fn (array $o) => ! in_array($o['slug'], $sent, true))));
        if ($new === []) {
            $this->info('Новых возможностей нет.');

            return self::SUCCESS;
        }

        $recipients = BotUser::where('notifications_muted', false)->whereNotNull('telegram_id')->pluck('telegram_id');

        foreach ($new as $o) {
            if ($this->expired($o)) {
                $this->line("{$o['slug']}: срок подачи заявок прошёл, не рассылаю");
                $dry || DB::table('opportunity_broadcasts')->insert(['slug' => $o['slug'], 'baseline' => true, 'created_at' => now(), 'updated_at' => now()]);

                continue;
            }

            $this->line("{$o['slug']}: {$recipients->count()} получателей".($dry ? ' (пробный запуск)' : ''));
            if ($dry) {
                $this->line($this->text($o));

                continue;
            }

            $ok = $failed = 0;
            $url = rtrim((string) config('app.url'), '/').'/app/account/opportunities/'.$o['slug'];
            $text = $this->text($o);

            foreach ($recipients as $chatId) {
                try {
                    $messenger->send((int) $chatId, $text, $url, 'Открыть в кабинете');
                    $ok++;
                } catch (\Throwable $e) {
                    $failed++;
                    Log::info('Рассылка возможностей: не доставлено', ['telegram_id' => $chatId, 'error' => $e->getMessage()]);
                }
                usleep(60000); // лимит Telegram около 30 сообщений в секунду
            }

            DB::table('opportunity_broadcasts')->insert(['slug' => $o['slug'], 'recipients' => $ok, 'failed' => $failed, 'created_at' => now(), 'updated_at' => now()]);
            $this->info("  доставлено {$ok}, не доставлено {$failed}");
        }

        return self::SUCCESS;
    }

    private function expired(array $o): bool
    {
        return ! empty($o['deadline']) && Carbon::parse($o['deadline'])->endOfDay()->isPast();
    }

    private function text(array $o): string
    {
        $where = trim(($o['flag'] ?? '').' '.($o['country']['ru'] ?? ''));
        $meta = implode(' · ', array_filter([$where, $o['dates']['ru'] ?? null]));
        $lines = ['🌍 Новая возможность', '', $o['title']['ru'] ?? $o['name'], $meta, '', $o['text']['ru'] ?? ($o['text']['en'] ?? '')];
        if (! empty($o['deadline_text']['ru'])) {
            $lines[] = '';
            $lines[] = 'Срок подачи заявок: '.$o['deadline_text']['ru'];
        }

        return implode("\n", $lines);
    }
}
