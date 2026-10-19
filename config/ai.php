<?php

declare(strict_types=1);

return [

    /*
     * Единый шлюз ко всем моделям — OpenRouter (https://openrouter.ai).
     * Один ключ на все задачи; модель для каждой задачи задаётся ниже
     * и может быть переопределена в админке (раздел «ИИ»).
     */
    'openrouter' => [
        'key'      => env('OPENROUTER_API_KEY'),
        'base_url' => env('OPENROUTER_BASE_URL', 'https://openrouter.ai/api/v1'),
        'referer'  => env('APP_URL', 'http://localhost'),
        'title'    => 'INSPIRE EDU Network',
        'timeout'  => 45,
    ],

    /*
     * Задачи ИИ. type: chat | embedding.
     * model/fallback — идентификаторы моделей OpenRouter (vendor/model).
     * Значения по умолчанию можно менять через .env или в админке.
     */
    'tasks' => [
        'embeddings' => [
            'label'    => 'Эмбеддинги (матчи и поиск)',
            'type'     => 'embedding',
            'model'    => env('AI_MODEL_EMBEDDINGS', 'openai/text-embedding-3-small'),
            'fallback' => env('AI_FALLBACK_EMBEDDINGS'),
        ],
        'quest_generate' => [
            'label'       => 'Квесты: составление вопроса',
            'type'        => 'chat',
            'model'       => env('AI_MODEL_QUEST_GENERATE', 'openai/gpt-4o-mini'),
            'fallback'    => env('AI_FALLBACK_QUEST_GENERATE'),
            'temperature' => 0.8,
            'max_tokens'  => 900,
        ],
        'quest_verify' => [
            'label'       => 'Квесты: автопроверка вопроса',
            'type'        => 'chat',
            'model'       => env('AI_MODEL_QUEST_VERIFY', 'openai/gpt-4o-mini'),
            'fallback'    => env('AI_FALLBACK_QUEST_VERIFY'),
            'temperature' => 0.0,
            'max_tokens'  => 400,
        ],
        'profile_prompt' => [
            'label'       => 'Подсказки «расскажи о себе»',
            'type'        => 'chat',
            'model'       => env('AI_MODEL_PROFILE_PROMPT', 'openai/gpt-4o-mini'),
            'fallback'    => env('AI_FALLBACK_PROFILE_PROMPT'),
            'temperature' => 0.9,
            'max_tokens'  => 300,
        ],
        'translate' => [
            'label'       => 'Перевод ru / en / ro',
            'type'        => 'chat',
            'model'       => env('AI_MODEL_TRANSLATE', 'openai/gpt-4o-mini'),
            'fallback'    => env('AI_FALLBACK_TRANSLATE'),
            'temperature' => 0.2,
            'max_tokens'  => 1200,
        ],
        'moderation' => [
            'label'       => 'Модерация текстов',
            'type'        => 'chat',
            'model'       => env('AI_MODEL_MODERATION', 'openai/gpt-4o-mini'),
            'fallback'    => env('AI_FALLBACK_MODERATION'),
            'temperature' => 0.0,
            'max_tokens'  => 200,
        ],
    ],

    /*
     * Top N matches to show per user.
     */
    'matches_count' => 5,

    /*
     * Cache TTL in seconds for computed matches (default 24h).
     */
    'matches_cache_ttl' => 86400,

    /*
     * Minimum cosine similarity score (0–1) to show in AI search results.
     * Results below this threshold are hidden as "not relevant".
     */
    'search_min_score' => 0.65,

];
