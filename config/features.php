<?php

declare(strict_types=1);

/*
 * Включение и выключение разделов кабинета. Код разделов остаётся на месте:
 * чтобы вернуть раздел, поставьте в .env значение true и выполните php artisan config:clear.
 */
return [
    'cabinet_matches' => (bool) env('FEATURE_CABINET_MATCHES', false), // AI-матчи
    'cabinet_search'  => (bool) env('FEATURE_CABINET_SEARCH', false),  // AI-поиск
    'cabinet_knowledge' => (bool) env('FEATURE_CABINET_KNOWLEDGE', false), // База знаний
];
