<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\QuestService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Throwable;

/** Генерация квестов по кнопке в админке: ИИ-запросы долгие, в веб-запросе они упираются в лимит времени PHP. */
class GenerateQuestsJob implements ShouldQueue
{
    use Queueable;

    public const FLAG   = 'game:generating';
    public const RESULT = 'game:generate:result';

    public int $tries   = 1;
    public int $timeout = 600;

    public function __construct(public readonly int $need) {}

    public function handle(QuestService $quests): void
    {
        $made  = 0;
        $error = null;
        try {
            $made = $quests->generate($this->need);
        } catch (Throwable $e) {
            $error = $e->getMessage();
        } finally {
            Cache::put(self::RESULT, ['made' => $made, 'need' => $this->need, 'error' => $error, 'at' => now()->toDateTimeString()], now()->addDay());
            Cache::forget(self::FLAG);
        }
    }

    public function failed(Throwable $e): void
    {
        Cache::put(self::RESULT, ['made' => 0, 'need' => $this->need, 'error' => $e->getMessage(), 'at' => now()->toDateTimeString()], now()->addDay());
        Cache::forget(self::FLAG);
    }
}
