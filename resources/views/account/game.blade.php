@extends('account.layout')
@section('title', $tr('Игра', 'Game', 'Joc'))

@push('head')
    @vite(['resources/js/game/main.js'])
@endpush

@php
    $api = [
        'state'       => route('account.game.api.state'),
        'facts'       => route('account.game.api.facts'),
        'quest'       => route('account.game.api.quest'),
        'network'     => route('account.game.api.network'),
    ];
@endphp

@section('content')
<div class="cab-page">
<div id="game-app" data-locked="{{ $locked }}" data-api="{{ json_encode($api) }}" data-i18n="{{ json_encode(\App\Support\Lang::game(), JSON_UNESCAPED_UNICODE) }}"></div>
</div>
@endsection
