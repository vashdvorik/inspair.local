@extends('account.layout')
@section('title', $tr('Главная', 'Home', 'Acasă'))

@php
    $stepsLeft = array_keys(array_filter($steps, fn ($done) => ! $done));
    $stepText = [
        'avatar'      => $tr('добавить фото', 'add a photo', 'adaugă o poză'),
        'description' => $tr('рассказать о себе', 'tell about yourself', 'povestește despre tine'),
    ];
    $ring = 2 * M_PI * 26;   // длина окружности индикатора
@endphp

@section('content')
<div class="cab-page cab-page--wide home">

    {{-- ── Профиль: что осталось заполнить ── --}}
    @if($stepsLeft)
        <a href="{{ route('account.profile.edit') }}" class="home-profile">
            <span class="home-ring" aria-hidden="true">
                <svg viewBox="0 0 64 64"><circle cx="32" cy="32" r="26" class="home-ring__bg"/><circle cx="32" cy="32" r="26" class="home-ring__fg" stroke-dasharray="{{ round($ring * $completeness / 100, 1) }} {{ round($ring, 1) }}"/></svg>
                <b>{{ $completeness }}%</b>
            </span>
            <span class="min-w-0 flex-1">
                <b>{{ $tr('Заполните профиль', 'Complete your profile', 'Completează profilul') }}</b>
                <small>{{ $tr('Осталось', 'Left to do', 'Mai ai de făcut') }}: {{ implode(', ', array_map(fn ($k) => $stepText[$k], $stepsLeft)) }}</small>
            </span>
            <svg class="home-profile__go h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M9 5l7 7-7 7"/></svg>
        </a>
    @endif

    {{-- ── Новые возможности ── --}}
    @if($opportunities)
        <section class="home-sec">
            <div class="home-sec__head">
                <h2>{{ $tr('Новые возможности', 'New opportunities', 'Oportunități noi') }}</h2>
                <a href="{{ route('account.opportunities.index') }}">{{ $tr('Все', 'All', 'Toate') }} · {{ $opportunitiesTotal }} →</a>
            </div>
            <div class="cab-opps">
                @foreach($opportunities as $o)
                    @include('account.opportunities._card')
                @endforeach
            </div>
        </section>
    @endif

    {{-- ── Сообщество: новые лица ── --}}
    @if($newMembers->isNotEmpty())
        <section class="home-sec">
            <div class="home-sec__head">
                <h2>{{ $tr('Новые участники', 'New members', 'Membri noi') }}</h2>
                <a href="{{ route('account.people') }}">{{ $tr('Все', 'All', 'Toți') }} →</a>
            </div>
            <div class="home-members">
                @foreach($newMembers->take(8) as $m)
                    <a href="{{ route('account.people.show', $m) }}" class="home-member">
                        @if($m->avatar_path)
                            <img src="{{ $m->avatarUrl() }}" alt="{{ $m->full_name }}" width="52" height="52" loading="lazy">
                        @else
                            <span class="home-member__letter">{{ mb_strtoupper(mb_substr((string) $m->full_name, 0, 1)) }}</span>
                        @endif
                        <span class="home-member__text">
                            <b>{{ $m->full_name }}</b>
                            @if(filled($m->description))<small>{{ \Illuminate\Support\Str::limit(trim((string) $m->description), 90) }}</small>@endif
                        </span>
                        <svg class="home-member__go h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M9 5l7 7-7 7"/></svg>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ── Игра ── --}}
    @if($progress)
        @php $lvl = $game->levelInfo($progress['level']); $nextLvl = $progress['next_from'] ? $game->levelInfo($progress['level'] + 1) : null; @endphp
        <a href="{{ route('account.game') }}" class="home-game">
            <span class="home-game__ico" style="background:{{ $lvl['color'] }}">{{ $lvl['n'] }}</span>
            <span class="min-w-0 flex-1">
                <b>{{ $tr('Угадай, кто это', 'Guess who it is', 'Ghicește cine e') }}</b>
                <small>{{ $lvl['name'] }} · {{ $progress['xp'] }} {{ $tr('очков', 'points', 'puncte') }}@if($nextLvl) · {{ $tr('до уровня', 'to level', 'până la nivelul') }} «{{ $nextLvl['name'] }}»: {{ $progress['next_from'] - $progress['xp'] }}@endif</small>
                <span class="home-game__bar"><i style="width:{{ $progress['percent'] }}%;background:{{ $lvl['color'] }}"></i></span>
            </span>
            <svg class="home-profile__go h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M9 5l7 7-7 7"/></svg>
        </a>
    @endif

    {{-- ── Остальные разделы ── --}}
    @php
        $links = [
            ['account.profile', $tr('Мой профиль', 'My profile', 'Profilul meu'), ''],
            ['account.matches', $tr('AI-матчи', 'AI matches', 'Potriviri AI'), config('features.cabinet_matches')],
            ['account.search', $tr('AI-поиск', 'AI search', 'Căutare AI'), config('features.cabinet_search')],
            ['account.knowledge', $tr('База знаний', 'Knowledge base', 'Baza de cunoștințe'), config('features.cabinet_knowledge')],
        ];
    @endphp
    <nav class="home-links" aria-label="{{ $tr('Разделы', 'Sections', 'Secțiuni') }}">
        @foreach($links as [$route, $label, $on])
            @continue($on === false || $on === null)
            <a href="{{ route($route) }}">{{ $label }}</a>
        @endforeach
    </nav>

</div>
@endsection
