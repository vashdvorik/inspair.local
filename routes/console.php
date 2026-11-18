<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Игра: пополнение пула квестов (когда игра запущена)
\Illuminate\Support\Facades\Schedule::command('game:generate-quests')->everyThirtyMinutes()->withoutOverlapping();

// Новые возможности с сайта уходят участникам в бот (каждая один раз)
\Illuminate\Support\Facades\Schedule::command('opportunities:notify')->everyFifteenMinutes()->withoutOverlapping();

// Рассылки, созданные администратором в админке: отправляются, когда придёт время
\Illuminate\Support\Facades\Schedule::command('broadcasts:send')->everyMinute()->withoutOverlapping();

// Нейросеть проверяет тексты участников и отмечает подозрительное для администратора
\Illuminate\Support\Facades\Schedule::command('moderation:scan')->everyFiveMinutes()->withoutOverlapping();

// Очередь (расчёт эмбеддингов профилей и т. п.) разбирается тем же cron-ом schedule:run: на хостинге постоянный воркер не нужен
\Illuminate\Support\Facades\Schedule::command('queue:work --stop-when-empty --max-time=55 --tries=2')->everyMinute()->withoutOverlapping();
