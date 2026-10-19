<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\Ai\OpenRouter;

class EmbeddingService
{
    public function __construct(private readonly OpenRouter $ai) {}

    /** @return array<int, float> */
    public function embed(string $text): array
    {
        return $this->ai->embed($text);
    }

    /** Модель, которой считаются новые векторы (пишется в bot_users.embedding_model). */
    public function model(): string
    {
        return $this->ai->embeddingModel();
    }

    /**
     * Build the text to embed for a BotUser.
     * Combines description + expectation for richer semantic signal.
     */
    public function textForUser(string $description, string $expectation): string
    {
        $parts = array_filter([
            trim($description),
            trim($expectation),
        ]);

        return implode("\n\n", $parts);
    }
}
