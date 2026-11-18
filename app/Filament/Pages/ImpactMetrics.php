<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\BotUser;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class ImpactMetrics extends Page
{
    protected string $view = 'filament.pages.impact-metrics';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Статистика';

    protected static ?string $title = 'Impact Metrics';

    protected static ?int $navigationSort = 2;

    /**
     * @return array<string, mixed>
     */
    public function getViewData(): array
    {
        // ── Members ──────────────────────────────────────────────────
        $totalMembers   = BotUser::count();
        $approvedCount  = BotUser::approved()->count();
        $bannedCount    = BotUser::banned()->count();
        $withProfile    = BotUser::approved()->whereNotNull('description')->where('description', '!=', '')->count();
        $withEmbedding  = BotUser::approved()->whereNotNull('embedding')->count();

        // ── Registration dynamics — last 12 months ────────────────────
        $registrationsByMonth = BotUser::query()
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                DB::raw('COUNT(*) as cnt')
            )
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('cnt', 'month')
            ->toArray();

        // Fill in any missing months with 0
        $registrationChart = [];
        for ($i = 11; $i >= 0; $i--) {
            $key = now()->subMonths($i)->format('Y-m');
            $registrationChart[$key] = $registrationsByMonth[$key] ?? 0;
        }

        // ── Latest members (for quick view) ──────────────────────────
        $latestMembers = BotUser::approved()
            ->latest('approved_at')
            ->limit(5)
            ->get(['full_name', 'telegram_username', 'approved_at', 'description']);

        return [
            'totalMembers'       => $totalMembers,
            'approvedCount'      => $approvedCount,
            'bannedCount'        => $bannedCount,
            'withProfile'        => $withProfile,
            'withEmbedding'      => $withEmbedding,
            'registrationChart'  => $registrationChart,
            'latestMembers'      => $latestMembers,
            'generatedAt'        => now()->format('d.m.Y H:i'),
        ];
    }
}
