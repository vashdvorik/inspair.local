<?php

declare(strict_types=1);

return [

    /*
     * Уровни. from — порог очков, unlocks — что открывается.
     * Цвета взяты из палитры логотипа (красный остаётся цветом ошибок).
     */
    'levels' => [
        1 => ['name' => ['ru' => 'Новичок',    'en' => 'Newcomer',   'ro' => 'Începător'],  'color' => '#019eff', 'from' => 0],
        2 => ['name' => ['ru' => 'Активист',   'en' => 'Activist',   'ro' => 'Activist'],   'color' => '#3d932e', 'from' => 100],
        3 => ['name' => ['ru' => 'Союзник',    'en' => 'Ally',       'ro' => 'Aliat'],      'color' => '#fdba07', 'from' => 300],
        4 => ['name' => ['ru' => 'Посредник',  'en' => 'Mediator',   'ro' => 'Mediator'],   'color' => '#ff6c0b', 'from' => 700],
        5 => ['name' => ['ru' => 'Амбассадор', 'en' => 'Ambassador', 'ro' => 'Ambasador'],  'color' => '#72299c', 'from' => 1500],
    ],

    /*
     * С какого уровня доступны разделы кабинета (после запуска игры).
     */
    'access' => [
        'people'        => 2,
        'search'        => 3,
        'matches'       => 3,
        'opportunities' => 4, // размещение возможностей
    ],

    /*
     * Что видно в профиле другого участника, по уровням.
     * Аватар и имя видны всегда (уровень 1).
     */
    'visibility' => [
        'region'      => 2,
        'description' => 3,
        'facts'       => 3,
        'expectation' => 4,
        'opportunities' => 5,
    ],

    /*
     * Очки.
     */
    'xp' => [
        'profile_avatar'      => 30,
        'profile_description' => 50,
        'profile_expectation' => 30,
        'profile_region'      => 10,
        'fact'                => 15, // за каждый факт, максимум max_facts
        'quest_correct'       => 20,
        'quest_wrong'         => 3,
        'mutual'              => 25,
    ],

    // Файл с режимом игры (off | waiting | live). Админка переключает его, база не нужна.
    'mode_file' => env('GAME_MODE_FILE', storage_path('app/game-mode.json')),

    'max_facts' => 10,

    // Сколько секунд ждать после промаха
    'miss_cooldown' => 8,

    // Для запуска игры нужно столько одобренных участников с заполненным «о себе» (показывается на экране ожидания)
    'launch_target' => 12,

    // Квесты: сколько штук держим в пуле на одного участника и сколько жалоб скрывают вопрос
    'pool_per_user'  => 2,
    'pool_min'       => 6,
    'generate_batch' => 8,
    'reports_hide'   => 2,

    // Напоминания бота: не чаще раза в N часов
    'remind_every_hours' => 20,
];
