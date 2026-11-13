@extends('account.layout')
@section('title', $section)

@section('content')
<div class="cab-page">
    <div>
        <h1>{{ $section }}</h1>
    </div>

    <div class="mod-card {{ $rejected ? 'mod-card--no' : '' }}">
        <div class="mod-ico" aria-hidden="true">
            @if($rejected)
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
            @else
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            @endif
        </div>

        @if($rejected)
            <h2>Модератор отказал вам в доступе к игре</h2>
            @if(filled($note))
                <p class="mod-note-label">Сообщение модератора</p>
                <blockquote class="mod-note">{!! nl2br(e($note)) !!}</blockquote>
            @endif
        @else
            <h2>Модератор ещё не посмотрел ваш профиль</h2>
            <p class="mod-text">Как только он проверит профиль, здесь откроются игра и список участников. Пока можно заполнить профиль: так проверка пройдёт быстрее.</p>
            <a href="{{ route('account.profile.edit') }}" class="i-btn" style="margin-top:18px">Заполнить профиль</a>
        @endif
    </div>
</div>
@endsection
