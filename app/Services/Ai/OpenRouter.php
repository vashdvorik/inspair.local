<?php

declare(strict_types=1);

namespace App\Services\Ai;

use App\Models\AiTaskSetting;
use App\Models\AiUsageLog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

/**
 * Единственная точка обращения к моделям.
 * Один ключ OpenRouter, модель выбирается по имени задачи (config/ai.php + переопределения из админки).
 */
class OpenRouter
{
    private const SETTINGS_CACHE_KEY = 'ai:task-settings';

    /** @return array<string, array<string, mixed>> все задачи с учётом переопределений */
    public function tasks(): array
    {
        $overrides = Cache::remember(self::SETTINGS_CACHE_KEY, 60, fn () => AiTaskSetting::all()->keyBy('task')->map->toArray()->all());

        $tasks = [];
        foreach ((array) config('ai.tasks') as $name => $def) {
            $o = $overrides[$name] ?? [];
            $tasks[$name] = [
                'task'             => $name,
                'label'            => $def['label'] ?? $name,
                'type'             => $def['type'] ?? 'chat',
                'model'            => $o['model'] ?? null ?: $def['model'],
                'fallback_model'   => $o['fallback_model'] ?? null ?: ($def['fallback'] ?? null),
                'temperature'      => $o['temperature'] ?? $def['temperature'] ?? null,
                'max_tokens'       => $o['max_tokens'] ?? $def['max_tokens'] ?? null,
                'daily_call_limit' => $o['daily_call_limit'] ?? null,
                'enabled'          => $o['enabled'] ?? true,
            ];
        }

        return $tasks;
    }

    /** @return array<string, mixed> */
    public function task(string $name): array
    {
        return $this->tasks()[$name] ?? throw new RuntimeException("Неизвестная ИИ-задача: {$name}");
    }

    public static function forgetSettings(): void
    {
        Cache::forget(self::SETTINGS_CACHE_KEY);
    }

    /**
     * Обычный запрос к чат-модели. Возвращает ['text', 'model'].
     *
     * @param  array<int, array{role: string, content: string}>  $messages
     * @param  array<string, mixed>  $extra  дополнительные поля запроса (response_format и т.д.)
     * @return array{text: string, model: string}
     */
    public function chat(string $task, array $messages, array $extra = []): array
    {
        $cfg = $this->task($task);
        $this->guard($cfg);

        $payload = array_filter([
            'messages'    => $messages,
            'temperature' => $cfg['temperature'],
            'max_tokens'  => $cfg['max_tokens'],
            'usage'       => ['include' => true],
        ], fn ($v) => $v !== null) + $extra;

        $res = $this->withFallback($cfg, '/chat/completions', $payload);

        $text = $res['body']['choices'][0]['message']['content'] ?? null;
        if (! is_string($text) || $text === '') {
            throw new RuntimeException('Модель вернула пустой ответ.');
        }

        return ['text' => $text, 'model' => $res['model']];
    }

    /**
     * Запрос, который должен вернуть JSON-объект. Возвращает уже разобранный массив.
     *
     * @param  array<int, array{role: string, content: string}>  $messages
     * @return array<string, mixed>
     */
    public function json(string $task, array $messages): array
    {
        $out  = $this->chat($task, $messages, ['response_format' => ['type' => 'json_object']]);
        $text = trim($out['text']);
        // Некоторые модели оборачивают JSON в ```-блок
        $text = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', $text) ?? $text;

        $data = json_decode($text, true);
        if (! is_array($data)) {
            throw new RuntimeException('Модель вернула невалидный JSON.');
        }

        return $data;
    }

    /**
     * Вектор для текста.
     *
     * @return array<int, float>
     */
    public function embed(string $text, string $task = 'embeddings'): array
    {
        $cfg = $this->task($task);
        $this->guard($cfg);

        $res = $this->withFallback($cfg, '/embeddings', ['input' => $text]);

        $vec = $res['body']['data'][0]['embedding'] ?? null;
        if (! is_array($vec) || $vec === []) {
            throw new RuntimeException('Неожиданный ответ эмбеддингов: ' . substr(json_encode($res['body']), 0, 300));
        }

        return array_map('floatval', $vec);
    }

    /** Модель, которой сейчас считаются эмбеддинги (для пометки векторов). */
    public function embeddingModel(): string
    {
        return (string) $this->task('embeddings')['model'];
    }

    // ───────────────────────── internals ─────────────────────────

    /** @param array<string, mixed> $cfg */
    private function guard(array $cfg): void
    {
        if (! config('ai.openrouter.key')) {
            throw new RuntimeException('Не задан OPENROUTER_API_KEY.');
        }
        if (! $cfg['enabled']) {
            throw new RuntimeException("Задача «{$cfg['label']}» отключена в настройках.");
        }
        if ($cfg['daily_call_limit']) {
            $today = AiUsageLog::where('task', $cfg['task'])->where('created_at', '>=', now()->startOfDay())->count();
            if ($today >= (int) $cfg['daily_call_limit']) {
                throw new RuntimeException("Достигнут дневной лимит обращений для «{$cfg['label']}».");
            }
        }
    }

    /**
     * Основная модель, при ошибке — запасная.
     *
     * @param  array<string, mixed>  $cfg
     * @param  array<string, mixed>  $payload
     * @return array{body: array<string, mixed>, model: string}
     */
    private function withFallback(array $cfg, string $path, array $payload): array
    {
        $models = array_values(array_filter([$cfg['model'], $cfg['fallback_model']]));
        $last   = null;

        foreach ($models as $i => $model) {
            $started = microtime(true);
            try {
                $body = $this->post($path, ['model' => $model] + $payload);
                $this->log($cfg['task'], $model, $i === 0 ? 'ok' : 'fallback', $body, $started);

                return ['body' => $body, 'model' => $model];
            } catch (Throwable $e) {
                $last = $e;
                $this->log($cfg['task'], $model, 'error', [], $started, $e->getMessage());
            }
        }

        throw $last ?? new RuntimeException('Не настроена модель для задачи.');
    }

    /** @return array<string, mixed> */
    private function post(string $path, array $payload): array
    {
        $cfg = config('ai.openrouter');

        $response = Http::withToken((string) $cfg['key'])
            ->withHeaders(['HTTP-Referer' => $cfg['referer'], 'X-Title' => $cfg['title']])
            ->timeout((int) $cfg['timeout'])
            ->retry(2, 400, fn ($e) => ! ($e instanceof \Illuminate\Http\Client\RequestException) || $e->response->serverError() || $e->response->status() === 429, throw: false)
            ->acceptJson()
            ->post(rtrim((string) $cfg['base_url'], '/') . $path, $payload);

        if (! $response->successful()) {
            throw new RuntimeException('OpenRouter ' . $response->status() . ': ' . substr($response->body(), 0, 300));
        }

        $json = $response->json();
        if (isset($json['error'])) {
            throw new RuntimeException('OpenRouter: ' . substr(json_encode($json['error'], JSON_UNESCAPED_UNICODE), 0, 300));
        }

        return is_array($json) ? $json : [];
    }

    /** @param array<string, mixed> $body */
    private function log(string $task, string $model, string $status, array $body, float $started, ?string $error = null): void
    {
        try {
            $usage = $body['usage'] ?? [];
            AiUsageLog::create([
                'task'              => $task,
                'model'             => $model,
                'status'            => $status,
                'prompt_tokens'     => (int) ($usage['prompt_tokens'] ?? 0),
                'completion_tokens' => (int) ($usage['completion_tokens'] ?? 0),
                'cost'              => isset($usage['cost']) ? (float) $usage['cost'] : null,
                'latency_ms'        => (int) round((microtime(true) - $started) * 1000),
                'error'             => $error ? substr($error, 0, 500) : null,
            ]);
        } catch (Throwable) {
            // журнал не должен ломать основную работу
        }
    }
}
