@extends('account.layout')
@section('title', $tr('Игра', 'Game', 'Joc'))

@push('head')
    @vite(['resources/js/game/main.js'])
@endpush

@php
    $api = [
        'state'       => route('account.game.api.state'),
        'quest'       => route('account.game.api.quest'),
        'network'     => route('account.game.api.network'),
    ];
@endphp

@section('content')
<div class="cab-page">
<div class="mb-6">
    <h1 class="text-2xl font-bold tracking-tight text-[#0e2442]">{{ $tr('Угадай, кто это', 'Guess who it is', 'Ghicește cine e') }}</h1>
    <p class="mt-1.5 text-sm text-gray-500">{{ $tr('Угадывайте участников по их рассказам, копите очки и открывайте новые уровни.', 'Guess participants from their stories, collect points and unlock new levels.', 'Ghicește participanții după poveștile lor, adună puncte și deblochează niveluri noi.') }}</p>
</div>

<div id="game-app" data-locked="{{ $locked }}" data-api="{{ json_encode($api) }}" data-i18n="{{ json_encode(\App\Support\Lang::game(), JSON_UNESCAPED_UNICODE) }}"></div>
</div>
@endsection
