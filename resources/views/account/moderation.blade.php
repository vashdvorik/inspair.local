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
            <h2>{{ $tr('Модератор отказал вам в доступе к игре', 'The moderator has denied you access to the game', 'Moderatorul ți-a refuzat accesul la joc') }}</h2>
            @if(filled($note))
                <p class="mod-note-label">{{ $tr('Сообщение модератора', 'Message from the moderator', 'Mesajul moderatorului') }}</p>
                <blockquote class="mod-note">{!! nl2br(e($note)) !!}</blockquote>
            @endif
        @else
            <h2>{{ $tr('Модератор ещё не посмотрел ваш профиль', 'The moderator has not reviewed your profile yet', 'Moderatorul nu ți-a verificat încă profilul') }}</h2>
            <p class="mod-text">{{ $tr('Как только он проверит профиль, здесь откроются игра и список участников. Пока можно заполнить профиль: так проверка пройдёт быстрее.', 'As soon as the profile is checked, the game and the list of participants will open here. Meanwhile you can fill in your profile: the check will go faster.', 'De îndată ce profilul este verificat, aici se vor deschide jocul și lista participanților. Între timp poți completa profilul: verificarea va merge mai repede.') }}</p>
            <a href="{{ route('account.profile.edit') }}" class="i-btn" style="margin-top:18px">{{ $tr('Заполнить профиль', 'Complete your profile', 'Completează profilul') }}</a>
        @endif
    </div>
</div>
@endsection
