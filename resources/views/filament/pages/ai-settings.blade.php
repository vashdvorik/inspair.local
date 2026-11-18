<x-filament-panels::page>

{{-- Ключ OpenRouter: вводится здесь, хранится в базе зашифрованным и главнее строки из .env --}}
<div class="rounded-xl border bg-white p-5 {{ $hasKey ? 'border-gray-200' : 'border-orange-200' }}">
    <p class="mb-1 font-semibold text-gray-800">Ключ OpenRouter</p>

    @if($hasKey)
        <p class="mb-3 text-sm text-gray-500">
            Ключ задан (<strong>{{ $keyTail }}</strong>),
            {{ $keySource === 'admin' ? 'введён здесь, в админке.' : 'взят из файла .env на сервере.' }}
            Чтобы заменить его, введите новый ниже: старый перестанет использоваться.
        </p>
    @else
        <p class="mb-3 text-sm text-orange-800">Ключ не задан, поэтому нейросеть не работает (вопросы игры, совпадения, поиск, проверка текстов). Создайте ключ на <strong>openrouter.ai</strong> («Keys» → «Create Key») и вставьте его ниже.</p>
    @endif

    <div class="flex flex-wrap items-end gap-3">
        <label class="min-w-[260px] flex-1 text-xs text-gray-500">{{ $hasKey ? 'Новый ключ' : 'Ключ' }}
            <input type="password" autocomplete="off" placeholder="sk-or-v1-…" wire:model="newKey" wire:keydown.enter="saveKey"
                   class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-800"></label>
        <button type="button" wire:click="saveKey" class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white">Сохранить ключ</button>
        @if($keySource === 'admin')
            <button type="button" wire:click="removeKey" wire:confirm="Удалить ключ, введённый в админке?" class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700">Удалить</button>
        @endif
    </div>
    <p class="mt-3 text-xs text-gray-400">Ключ хранится в базе в зашифрованном виде и после сохранения нигде не показывается целиком (только последние 4 знака). Никому его не пересылайте. Если ключ попал в чужие руки, удалите его на openrouter.ai и создайте новый.</p>
</div>

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
