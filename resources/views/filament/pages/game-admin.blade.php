<x-filament-panels::page>

@php
    $modes = [
        'off'     => ['Выключена', 'Игры нет нигде: ни в меню, ни в профиле, ни в боте. «Люди», «Поиск» и «Матчи» открыты всем, как раньше.', 'gray'],
        'waiting' => ['Ожидание', 'Участники видят экран «игра скоро откроется» и заполняют профиль. «Люди», «Поиск», «Матчи» закрыты. Бот молчит.', 'orange'],
        'live'    => ['Запущена', 'Квесты, сеть и уровни работают, разделы открываются по уровням, бот присылает напоминания.', 'green'],
    ];
@endphp

<div class="rounded-xl border border-gray-200 bg-white p-5">
    <p class="mb-1 text-lg font-bold text-gray-800">Режим игры: {{ $modes[$mode][0] }}</p>
    <p class="mb-4 text-sm text-gray-500">Участников с заполненным профилем: <b>{{ $ready }}</b> из рекомендуемых {{ $target }}. Подходят для квестов: <b>{{ $eligible }}</b>.</p>

    <div class="grid gap-3 md:grid-cols-3">
        @foreach($modes as $key => [$title, $text, $tone])
            <button type="button"
                    @if($key !== $mode) wire:click="setMode('{{ $key }}')" wire:confirm="Переключить игру в режим «{{ $title }}»?" @endif
                    class="rounded-xl border p-4 text-left {{ $key === $mode ? 'border-blue-500 bg-blue-50' : 'border-gray-200 bg-white hover:border-gray-300' }}">
                <p class="font-semibold text-gray-800">{{ $title }} @if($key === $mode)<span class="text-xs font-normal text-blue-600">· сейчас</span>@endif</p>
                <p class="mt-1 text-xs text-gray-500">{{ $text }}</p>
            </button>
        @endforeach
    </div>
    <p class="mt-4 text-xs text-gray-400">Данные игры (очки, факты, нити, квесты) при выключении не удаляются: они просто никому не видны и вернутся при повторном включении.</p>
</div>

@if($mode !== 'off')
<div class="grid grid-cols-2 gap-4 lg:grid-cols-5">
    @foreach([['Активных квестов', $activeQuests], ['Ответов', $attempts], ['Точность', $accuracy . '%'], ['Нитей', $connections], ['Взаимных', $mutual]] as [$label, $value])
        <div class="rounded-xl border border-gray-200 bg-white p-4">
            <p class="text-2xl font-bold text-gray-800">{{ $value }}</p>
            <p class="text-xs text-gray-500">{{ $label }}</p>
        </div>
    @endforeach
</div>

<div class="rounded-xl border border-gray-200 bg-white p-5">
    <p class="mb-3 font-semibold text-gray-800">Участники по уровням</p>
    <div class="flex flex-wrap gap-3">
        @foreach($levels as $l)
            <span class="inline-flex items-center gap-2 rounded-full border border-gray-200 px-3 py-1.5 text-sm"><i class="inline-block h-2.5 w-2.5 rounded-full" style="background:{{ $l['color'] }}"></i>{{ $l['name'] }}: <b>{{ $l['count'] }}</b></span>
        @endforeach
    </div>
</div>

<div class="rounded-xl border border-gray-200 bg-white p-5">
    <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
        <p class="font-semibold text-gray-800">Квесты и жалобы</p>
        <button type="button" wire:click="generate" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700">Создать квесты сейчас</button>
    </div>
    @forelse($reported as $q)
        <div class="flex flex-wrap items-start justify-between gap-3 border-t border-gray-100 py-3">
            <div class="min-w-0">
                <p class="text-sm font-medium text-gray-800">{{ $q->question['ru'] ?? '' }}</p>
                <p class="text-xs text-gray-500">Ответ: {{ $q->subject?->full_name }} · жалоб: {{ $q->reports_count }} · статус: {{ $q->status }}</p>
            </div>
            <div class="flex gap-2">
                @if($q->status !== 'active')
                    <button type="button" wire:click="restoreQuest({{ $q->id }})" class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-semibold">Вернуть в игру</button>
                @endif
                @if($q->status !== 'rejected')
                    <button type="button" wire:click="rejectQuest({{ $q->id }})" class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600">Удалить из игры</button>
                @endif
            </div>
        </div>
    @empty
        <p class="text-sm text-gray-500">Жалоб пока нет.</p>
    @endforelse
</div>
@endif

<div class="rounded-xl border border-red-200 bg-white p-5">
    <p class="font-semibold text-gray-800">Стереть все данные игры</p>
    <p class="mt-1 text-sm text-gray-500">Удаляет очки, уровни, факты о себе, нити сети и квесты безвозвратно. Доступно, когда игра выключена или в ожидании. Настройки приватности участников сохраняются.</p>
    <button type="button" wire:click="wipe" wire:confirm="Стереть ВСЕ данные игры? Это нельзя отменить."
            class="mt-3 rounded-lg border border-red-300 px-4 py-2 text-sm font-semibold text-red-600">Стереть данные игры</button>
</div>

</x-filament-panels::page>
