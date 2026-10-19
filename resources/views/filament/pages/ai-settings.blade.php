<x-filament-panels::page>

@unless($hasKey)
    <div class="rounded-xl border border-orange-200 bg-orange-50 p-4 text-sm text-orange-800">
        Ключ не задан. Добавьте в <code>.env</code> строку <code>OPENROUTER_API_KEY=…</code> и выполните <code>php artisan config:clear</code>.
    </div>
@endunless

<div class="rounded-xl border border-gray-200 bg-white p-5">
    <p class="mb-1 text-sm text-gray-500">Один ключ OpenRouter на все задачи. Для каждой задачи укажите модель в формате <code>vendor/model</code> (например <code>openai/gpt-4o-mini</code>) и, при желании, запасную: она включится, если основная не ответит.</p>
    <p class="mb-5 text-sm text-gray-500">За 7 дней: <strong>{{ number_format($totalCost, 4) }} $</strong> по данным OpenRouter.</p>

    <div class="space-y-4">
        @foreach($rows as $task => $row)
            @php $s = $stats[$task] ?? null; @endphp
            <div class="rounded-lg border border-gray-100 bg-gray-50 p-4">
                <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <p class="font-semibold text-gray-800">{{ $row['label'] }}</p>
                        <p class="text-xs text-gray-400">{{ $task }} · {{ $row['type'] === 'embedding' ? 'эмбеддинги' : 'чат' }}
                            @if($s) · 7 дн.: {{ $s->calls }} вызовов, {{ $s->errors }} ошибок, {{ number_format((int) $s->tokens) }} токенов, {{ number_format((float) $s->cost, 4) }} $ @endif
                        </p>
                    </div>
                    <label class="flex items-center gap-2 text-sm"><input type="checkbox" wire:model="rows.{{ $task }}.enabled"> включено</label>
                </div>
                <div class="grid gap-3 md:grid-cols-4">
                    <label class="text-xs text-gray-500 md:col-span-2">Модель
                        <input type="text" wire:model="rows.{{ $task }}.model" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-800"></label>
                    <label class="text-xs text-gray-500 md:col-span-2">Запасная модель
                        <input type="text" wire:model="rows.{{ $task }}.fallback_model" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-800"></label>
                    @if($row['type'] === 'chat')
                        <label class="text-xs text-gray-500">Температура
                            <input type="number" step="0.1" min="0" max="2" wire:model="rows.{{ $task }}.temperature" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></label>
                        <label class="text-xs text-gray-500">Макс. токенов
                            <input type="number" min="16" wire:model="rows.{{ $task }}.max_tokens" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></label>
                    @endif
                    <label class="text-xs text-gray-500">Лимит вызовов в день
                        <input type="number" min="1" placeholder="без лимита" wire:model="rows.{{ $task }}.daily_call_limit" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></label>
                    <div class="flex items-end"><button type="button" wire:click="test('{{ $task }}')" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700">Проверить</button></div>
                </div>
                @if(!empty($testResults[$task]))
                    <p class="mt-3 text-sm {{ str_starts_with($testResults[$task], '✓') ? 'text-green-700' : 'text-red-600' }}">{{ $testResults[$task] }}</p>
                @endif
            </div>
        @endforeach
    </div>

    <div class="mt-6"><button type="button" wire:click="save" class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white">Сохранить</button></div>
</div>

@if($recentErrors->isNotEmpty())
    <div class="rounded-xl border border-gray-200 bg-white p-5">
        <p class="mb-3 font-semibold text-gray-800">Последние ошибки</p>
        <ul class="space-y-2 text-xs text-gray-600">
            @foreach($recentErrors as $e)
                <li><span class="text-gray-400">{{ $e->created_at->format('d.m H:i') }}</span> · <strong>{{ $e->task }}</strong> · {{ $e->model }} — {{ $e->error }}</li>
            @endforeach
        </ul>
    </div>
@endif

</x-filament-panels::page>
