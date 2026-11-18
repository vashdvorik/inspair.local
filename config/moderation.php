<?php

declare(strict_types=1);

return [

    /*
     * Кому в Telegram приходят предупреждения нейросети о подозрительных текстах участников.
     * Получают: участники с отметкой «Модератор / организатор» (в админке «Участники») и все ID из этого списка.
     * TELEGRAM_ADMIN_IDS: ID через запятую (свой ID показывает бот @userinfobot; человек должен нажать /start в нашем боте).
     */
    'admin_telegram_ids' => array_values(array_filter(array_map('trim', explode(',', (string) env('TELEGRAM_ADMIN_IDS', ''))), 'ctype_digit')),

];
