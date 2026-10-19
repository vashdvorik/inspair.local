<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\AiTaskSetting;
use App\Models\AiUsageLog;
use App\Services\Ai\OpenRouter;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class AiSettings extends Page
{
    protected string $view = 'filament.pages.ai-settings';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $navigationLabel = 'ИИ';

    protected static ?string $title = 'ИИ: модели и расход';

    protected static ?int $navigationSort = 3;

    /** @var array<string, array<string, mixed>> */
    public array $rows = [];

    /** @var array<string, string> */
    public array $testResults = [];

    public function mount(OpenRouter $ai): void
    {
        $this->rows = $ai->tasks();
    }

    public function save(OpenRouter $ai): void
    {
        $this->persist($ai);

        Notification::make()->title('Настройки сохранены')->success()->send();
    }

    public function test(string $task, OpenRouter $ai): void
    {
        // проверяем те значения, что сейчас в форме
        $this->persist($ai);

        try {
            if (($this->rows[$task]['type'] ?? 'chat') === 'embedding') {
                $dim = count($ai->embed('проверка связи', $task));
                $this->testResults[$task] = "✓ вектор из {$dim} чисел";
            } else {
                $r = $ai->chat($task, [['role' => 'user', 'content' => 'Ответь одним словом: «работает».']]);
                $this->testResults[$task] = '✓ ' . mb_substr(trim($r['text']), 0, 60) . ' (' . $r['model'] . ')';
            }
        } catch (\Throwable $e) {
            $this->testResults[$task] = '✗ ' . mb_substr($e->getMessage(), 0, 200);
        }
    }

    private function persist(OpenRouter $ai): void
    {
        foreach ($this->rows as $task => $row) {
            AiTaskSetting::updateOrCreate(['task' => $task], [
                'model'            => trim((string) ($row['model'] ?? '')) ?: null,
                'fallback_model'   => trim((string) ($row['fallback_model'] ?? '')) ?: null,
                'temperature'      => ($row['temperature'] ?? '') === '' ? null : (float) $row['temperature'],
                'max_tokens'       => ($row['max_tokens'] ?? '') === '' ? null : (int) $row['max_tokens'],
                'daily_call_limit' => ($row['daily_call_limit'] ?? '') === '' ? null : (int) $row['daily_call_limit'],
                'enabled'          => (bool) ($row['enabled'] ?? true),
            ]);
        }
        OpenRouter::forgetSettings();
        $this->rows = $ai->tasks();
    }

    /** @return array<string, mixed> */
    public function getViewData(): array
    {
        $stats = AiUsageLog::query()
            ->where('created_at', '>=', now()->subDays(7))
            ->select(
                'task',
                DB::raw('count(*) as calls'),
                DB::raw("sum(status = 'error') as errors"),
                DB::raw('sum(prompt_tokens + completion_tokens) as tokens'),
                DB::raw('coalesce(sum(cost), 0) as cost'),
            )
            ->groupBy('task')->get()->keyBy('task');

        return [
            'stats'        => $stats,
            'recentErrors' => AiUsageLog::where('status', 'error')->latest('id')->limit(8)->get(),
            'hasKey'       => (bool) config('ai.openrouter.key'),
            'totalCost'    => (float) $stats->sum('cost'),
        ];
    }
}
