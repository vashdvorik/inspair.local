<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Broadcast;
use App\Models\BroadcastDelivery;
use App\Services\BroadcastMessenger;
use Illuminate\Console\Command;

/**
 * Отправляет сообщения администратора, время которых пришло (раздел «Рассылки» в админке).
 * Запускается по расписанию каждую минуту. Каждому получателю сообщение уходит один раз: доставка записывается
 * в broadcast_deliveries, поэтому после сбоя запуск продолжает с того же места.
 */
class SendBroadcasts extends Command
{
    protected $signature = 'broadcasts:send';

    protected $description = 'Отправляет запланированные рассылки администратора участникам в бот';

    public function handle(BroadcastMessenger $messenger): int
    {
        $due = Broadcast::whereIn('status', [Broadcast::SCHEDULED, Broadcast::SENDING])
            ->where(fn ($q) => $q->whereNull('scheduled_at')->orWhere('scheduled_at', '<=', now()))
            ->orderBy('id')
            ->get();

        foreach ($due as $b) {
            $b->update(['status' => Broadcast::SENDING]);
            $done = BroadcastDelivery::where('broadcast_id', $b->id)->pluck('telegram_id')->all();

            $b->recipients()->whereNotIn('telegram_id', $done)->orderBy('id')->pluck('telegram_id')->each(function ($chatId) use ($b, $messenger): void {
                $error = null;
                try {
                    $messenger->send((int) $chatId, $b);
                } catch (\Throwable $e) {
                    $error = mb_substr($e->getMessage(), 0, 250);
                }
                BroadcastDelivery::create(['broadcast_id' => $b->id, 'telegram_id' => $chatId, 'delivered' => $error === null, 'error' => $error]);
                usleep(60000); // лимит Telegram около 30 сообщений в секунду
            });

            $b->update([
                'status'           => Broadcast::SENT,
                'sent_at'          => now(),
                'recipients_count' => BroadcastDelivery::where('broadcast_id', $b->id)->where('delivered', true)->count(),
                'failed_count'     => BroadcastDelivery::where('broadcast_id', $b->id)->where('delivered', false)->count(),
            ]);
            $this->info("Рассылка #{$b->id} «{$b->title}»: доставлено {$b->recipients_count}, не доставлено {$b->failed_count}");
        }

        return self::SUCCESS;
    }
}
