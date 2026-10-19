<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\GameService;
use App\Services\QuestService;
use Illuminate\Console\Command;

class GenerateQuests extends Command
{
    protected $signature = 'game:generate-quests {--count= : Сколько квестов создать (по умолчанию — сколько не хватает до нормы пула)} {--force : Даже если игра не запущена}';

    protected $description = 'Пополнить пул квестов: ИИ составляет вопросы по фактам участников';

    public function handle(QuestService $quests, GameService $game): int
    {
        if (! $game->isLaunched() && ! $this->option('force')) {
            $this->info('Игра ещё не запущена: квесты не создаются (используйте --force для проверки).');

            return self::SUCCESS;
        }

        $need = $this->option('count') !== null ? (int) $this->option('count') : min($quests->poolDeficit(), (int) config('game.generate_batch'));
        if ($need <= 0) {
            $this->info('Пул в норме или участников пока мало (нужно минимум 3).');

            return self::SUCCESS;
        }

        $created = $quests->generate($need);
        $this->info("Создано квестов: {$created} из {$need}.");

        return self::SUCCESS;
    }
}
