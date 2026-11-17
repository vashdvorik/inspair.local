@extends('account.layout')
@section('title', $tr('Главная', 'Home', 'Acasă'))

@section('content')
<div class="cab-page cab-page--wide">

    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-2xl font-bold tracking-tight text-[#0e2442]">
            {{ $tr('Привет', 'Hi', 'Salut') }}, {{ explode(' ', (string) $accountUser->full_name)[0] }}
        </h1>
        <p class="mt-1.5 text-sm text-gray-500">{{ $tr('Добро пожаловать в личный кабинет INSPIRE Community', 'Welcome to your INSPIRE Community account', 'Bine ai venit în contul tău INSPIRE Community') }}</p>
    </div>

    {{-- Profile completeness banner --}}
    @if(empty($accountUser->description))
    <div class="mb-8 flex items-start gap-4 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4">
        <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-100">
            <svg class="h-4 w-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-amber-900">{{ $tr('Заполни профиль', 'Complete your profile', 'Completează-ți profilul') }}</p>
            <p class="mt-0.5 text-xs text-amber-700 leading-relaxed">
                {{ $tr('Расскажи о себе — алгоритм использует эти данные для подбора партнёров.', 'Tell us about yourself — the algorithm uses this to suggest partners.', 'Povestește despre tine — algoritmul folosește aceste date pentru a-ți sugera parteneri.') }}
            </p>
        </div>
        <a href="{{ route('account.profile') }}"
           class="shrink-0 rounded-lg bg-amber-500 px-3.5 py-2 text-xs font-semibold text-white transition hover:bg-amber-600 active:scale-95">
            {{ $tr('Заполнить', 'Fill in', 'Completează') }}
        </a>
    </div>
    @endif

    {{-- Sections --}}
    @php
        $tiles = [
            ['account.profile',               $tr('Мой профиль', 'My profile', 'Profilul meu'), $tr('Обновить данные о себе', 'Update your details', 'Actualizează-ți datele'),            '',       'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
            ['account.game',                  $tr('Угадай, кто это', 'Guess who it is', 'Ghicește cine e'), $tr('Игра: угадывайте участников и копите очки', 'A game: guess participants and collect points', 'Joc: ghicește participanții și adună puncte'), 'orange', 'M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z'],
            ['account.matches',               $tr('AI-матчи', 'AI matches', 'Potriviri AI'), $tr('Рекомендованные партнёры', 'Recommended partners', 'Parteneri recomandați'),          'yellow', 'M13 10V3L4 14h7v7l9-11h-7z'],
            ['account.search',                $tr('AI-поиск', 'AI search', 'Căutare AI'), $tr('Найти нужного человека по описанию', 'Find the right person by description', 'Găsește persoana potrivită după descriere'), 'purple', 'M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z'],
            ['account.people',                $tr('Люди', 'People', 'Oameni'), $tr('Участники сообщества', 'Community members', 'Membrii comunității'),              'green',  'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
            ['account.opportunities.index',   $tr('Возможности', 'Opportunities', 'Oportunități'), $tr('Курсы и мероприятия за рубежом', 'Courses and events abroad', 'Cursuri și evenimente în străinătate'),    'orange', 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
            ['account.knowledge',             $tr('База знаний', 'Knowledge base', 'Baza de cunoștințe'), $tr('Материалы для участников', 'Materials for participants', 'Materiale pentru participanți'),          '',       'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
        ];
    @endphp
    <div class="cab-tiles">
        @foreach($tiles as [$route, $title, $hint, $tone, $path])
            @continue($route === 'account.game' && ! $game->isEnabled())
            @continue($route === 'account.matches' && ! config('features.cabinet_matches'))
            @continue($route === 'account.search' && ! config('features.cabinet_search'))
            @continue($route === 'account.knowledge' && ! config('features.cabinet_knowledge'))
            <a href="{{ route($route) }}" class="cab-tile">
                <span class="cab-tile__ico {{ $tone ? 'cab-tile__ico--' . $tone : '' }}">
                    <svg class="h-[22px] w-[22px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="{{ $path }}"/></svg>
                </span>
                <span><b>{{ $title }}</b><small>{{ $hint }}</small></span>
                <svg class="cab-tile__go h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M9 5l7 7-7 7"/></svg>
            </a>
        @endforeach
    </div>

</div>
@endsection
