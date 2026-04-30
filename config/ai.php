<?php

declare(strict_types=1);

return [

    'gemini' => [
        'key'             => env('GEMINI_API_KEY'),
        'embedding_model' => 'gemini-embedding-001',
        'base_url'        => 'https://generativelanguage.googleapis.com/v1beta',
    ],

    /*
     * Top N matches to show per user.
     */
    'matches_count' => 5,

    /*
     * Cache TTL in seconds for computed matches (default 24h).
     */
    'matches_cache_ttl' => 86400,

];
