<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Игра: пополнение пула квестов (когда игра запущена)
\Illuminate\Support\Facades\Schedule::command('game:generate-quests')->everyThirtyMinutes()->withoutOverlapping();
