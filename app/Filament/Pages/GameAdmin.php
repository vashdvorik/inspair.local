<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\BotUser;
use App\Models\Connection;
use App\Models\Quest;
use App\Models\QuestAttempt;
use App\Services\GameService;
use App\Services\QuestService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class GameAdmin extends Page
{
    protected string $view = 'filament.pages.game-admin';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-puzzle-piece';

    protected static ?string $navigationLabel = 'Игра «Нити»';

    protected static ?string $title = 'Игра «Нити»';

    protected static ?int $navigationSort = 4;

    public function setMode(string $mode, GameService $game): void
    {
        $game->setMode($mode);

        Notification::make()->title([
            GameService::MODE_OFF     => 'Игра выключена: кабинет такой, как был до неё',
            GameService::MODE_WAITING => 'Включён режим ожидания: разделы закрыты, участники видят экран ожидания',
            GameService::MODE_LIVE    => 'Игра запущена: участники видят квесты и лестницу уровней',
        ][$game->mode()])->success()->send();
    }

    public function wipe(GameService $game): void
    {
        if ($game->isLaunched()) {
            Notification::make()->title('Сначала выключите игру или переведите её в ожидание')->warning()->send();

            return;
        }

        $game->wipeData();
        Notification::make()->title('Данные игры стёрты: очки, факты, нити и квесты удалены')->success()->send();
    }

    public function generate(QuestService $quests): void
    {
        $need = max(1, min($quests->poolDeficit() ?: 3, (int) config('game.generate_batch')));
        try {
            $made = $quests->generate($need);
            Notification::make()->title("Создано квестов: {$made}")->{$made > 0 ? 'success' : 'warning'}()
                ->body($made > 0 ? null : 'Не получилось: проверьте ключ и модели на странице «ИИ», нужно минимум 3 участника с заполненным профилем.')->send();
        } catch (\Throwable $e) {
            Notification::make()->title('Ошибка генерации')->body($e->getMessage())->danger()->send();
        }
    }

    public function rejectQuest(int $id): void
    {
        Quest::whereKey($id)->update(['status' => Quest::REJECTED]);
    }

    public function restoreQuest(int $id): void
    {
        Quest::whereKey($id)->update(['status' => Quest::ACTIVE, 'reports_count' => 0]);
    }

    /** @return array<string, mixed> */
    public function getViewData(): array
    {
        $game = app(GameService::class);
        $quests = app(QuestService::class);
        $attempts = QuestAttempt::count();

        return [
            'mode'       => $game->mode(),
            'launched'   => $game->isLaunched(),
            'ready'      => $game->readyParticipants(),
            'target'     => (int) config('game.launch_target'),
            'eligible'   => $quests->eligibleUsers()->count(),
            'activeQuests' => Quest::active()->count(),
            'attempts'   => $attempts,
            'accuracy'   => $attempts ? round(QuestAttempt::where('correct', true)->count() / $attempts * 100) : 0,
            'connections' => Connection::count(),
            'mutual'     => (int) (Connection::where('mutual', true)->count() / 2),
            'levels'     => collect($game->levels())->map(fn ($def, $n) => [
                'name' => $def['name']['ru'], 'color' => $def['color'], 'count' => BotUser::approved()->where('level', $n)->count(),
            ])->values(),
            'reported'   => Quest::whereIn('status', [Quest::REPORTED])->orWhere('reports_count', '>', 0)->with('subject')->latest()->limit(20)->get(),
        ];
    }
}
