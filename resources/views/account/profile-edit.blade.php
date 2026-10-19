@extends('account.layout')
@section('title', 'Редактировать профиль')

@section('content')
<div class="max-w-2xl">

    {{-- Header --}}
    <div class="mb-8 flex items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-[#0e2442]">Редактировать профиль</h1>
            <p class="mt-1.5 text-sm text-gray-500">Изменения сразу видны другим участникам</p>
        </div>
        <a href="{{ route('account.profile') }}"
           class="inline-flex h-10 shrink-0 items-center gap-2 rounded-xl border border-gray-200 bg-white px-5
                  text-sm font-semibold text-gray-500 shadow-sm transition hover:border-gray-300 hover:text-gray-700">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Назад
        </a>
    </div>

    <form action="{{ route('account.profile.update') }}" method="POST" class="space-y-5">
        @csrf

        {{-- Identity --}}
        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <h2 class="mb-5 text-xs font-semibold uppercase tracking-widest text-gray-400">Личные данные</h2>
            <div class="space-y-5">

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-[#0e2442]">
                        Имя и фамилия <span class="text-red-400">*</span>
                    </label>
                    <input type="text" name="full_name"
                           value="{{ old('full_name', $accountUser->full_name) }}"
                           maxlength="120" required
                           placeholder="Иван Иванов"
                           class="w-full rounded-xl border px-4 py-3 text-sm text-[#0e2442] placeholder-gray-300
                                  transition duration-150 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent
                                  {{ $errors->has('full_name') ? 'border-red-300 bg-red-50' : 'border-gray-200 bg-gray-50 focus:bg-white' }}">
                    @error('full_name')
                    <p class="mt-1.5 flex items-center gap-1 text-xs text-red-600">
                        <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ $message }}
                    </p>
                    @enderror
                </div>

                <div class="rounded-xl border border-gray-100 bg-gray-50 px-4 py-3">
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-400">Telegram</p>
                    <p class="mt-1 text-sm font-medium">
                        @if($accountUser->telegram_username)
                        <a href="https://t.me/{{ $accountUser->telegram_username }}" target="_blank"
                           class="text-brand-600 hover:underline">
                            {{ '@' . $accountUser->telegram_username }}
                        </a>
                        @else
                        <span class="text-gray-400">Не указан</span>
                        @endif
                    </p>
                </div>

            </div>
        </div>

        {{-- About --}}
        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <h2 class="mb-5 text-xs font-semibold uppercase tracking-widest text-gray-400">О себе</h2>
            <div class="space-y-5">

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-[#0e2442]">
                        Кто ты и чем занимаешься?
                    </label>
                    <textarea name="description" rows="4" maxlength="1000"
                              placeholder="Роль, сфера, компания. Ссылки приветствуются."
                              class="w-full resize-none rounded-xl border px-4 py-3 text-sm text-[#0e2442] placeholder-gray-300
                                     transition duration-150 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent
                                     {{ $errors->has('description') ? 'border-red-300 bg-red-50' : 'border-gray-200 bg-gray-50 focus:bg-white' }}">{{ old('description', $accountUser->description) }}</textarea>
                    <p class="mt-1.5 text-xs text-gray-400">Используется алгоритмом для подбора партнёров · до 1000 символов</p>
                    @error('description')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-[#0e2442]">
                        Что ждёшь от сообщества?
                    </label>
                    <textarea name="expectation" rows="3" maxlength="1000"
                              placeholder="Чего ищешь и чем можешь быть полезен."
                              class="w-full resize-none rounded-xl border px-4 py-3 text-sm text-[#0e2442] placeholder-gray-300
                                     transition duration-150 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent
                                     {{ $errors->has('expectation') ? 'border-red-300 bg-red-50' : 'border-gray-200 bg-gray-50 focus:bg-white' }}">{{ old('expectation', $accountUser->expectation) }}</textarea>
                    @error('expectation')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

            </div>
        </div>

        {{-- Facts (feed the quests) --}}
        @if($game->isEnabled())
        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <h2 class="mb-1 text-xs font-semibold uppercase tracking-widest text-gray-400">Факты о себе</h2>
            <p class="mb-5 text-xs text-gray-500">Короткие и живые: чем занимаешься в свободное время, о чём мечтаешь, что умеешь. За каждый факт <b>+{{ config('game.xp.fact') }} очков</b>, а из них получаются загадки для других участников.</p>
            @php
                $facts = $accountUser->facts;
                $slots = max(0, min(config('game.max_facts') - $facts->count(), 3));
            @endphp
            <div class="space-y-3">
                @foreach($facts as $f)
                    <input type="text" name="facts[{{ $f->id }}]" value="{{ old('facts.'.$f->id, $f->body) }}" maxlength="300"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-[#0e2442] focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white">
                @endforeach
                @for($i = 0; $i < $slots; $i++)
                    <input type="text" name="new_facts[]" maxlength="300" placeholder="Например: перевожу субтитры к старым фильмам"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-[#0e2442] placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white">
                @endfor
            </div>
            <p class="mt-2 text-xs text-gray-400">Очистите поле, чтобы удалить факт. Всего до {{ config('game.max_facts') }}.</p>
        </div>

        @endif

        {{-- Privacy --}}
        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <h2 class="mb-5 text-xs font-semibold uppercase tracking-widest text-gray-400">Приватность</h2>
            <div class="space-y-4">
                <label class="flex items-start gap-3 text-sm text-[#0e2442]">
                    <input type="checkbox" name="contacts_closed" value="1" class="mt-0.5 h-4 w-4 rounded border-gray-300" {{ old('contacts_closed', $accountUser->contacts_closed) ? 'checked' : '' }}>
                    <span><b>Закрыть мои контакты</b><br><span class="text-xs text-gray-500">Мой Telegram и кнопка «Написать» не показываются другим участникам. Я по-прежнему могу писать им сам.</span></span>
                </label>
                @if($game->isEnabled())
                <label class="flex items-start gap-3 text-sm text-[#0e2442]">
                    <input type="checkbox" name="quests_opt_out" value="1" class="mt-0.5 h-4 w-4 rounded border-gray-300" {{ old('quests_opt_out', $accountUser->quests_opt_out) ? 'checked' : '' }}>
                    <span><b>Не использовать меня в квестах</b><br><span class="text-xs text-gray-500">Мои факты не попадут в загадки, и другие не смогут угадывать меня.</span></span>
                </label>
                <label class="flex items-start gap-3 text-sm text-[#0e2442]">
                    <input type="checkbox" name="notifications_muted" value="1" class="mt-0.5 h-4 w-4 rounded border-gray-300" {{ old('notifications_muted', $accountUser->notifications_muted) ? 'checked' : '' }}>
                    <span><b>Тише: без напоминаний в Telegram</b><br><span class="text-xs text-gray-500">Бот перестанет присылать напоминания об игре.</span></span>
                </label>
                @endif
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-between">
            <button type="submit"
                class="inline-flex h-11 items-center gap-2 rounded-xl px-6 text-sm font-semibold text-white
                       transition-all duration-150 hover:-translate-y-px hover:shadow-lg active:translate-y-0"
                style="background:var(--grad-primary);box-shadow:0 4px 14px rgba(0,109,224,.3)">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Сохранить изменения
            </button>
            
        </div>

    </form>

</div>
@endsection
