@extends('account.layout')
@section('title', 'Игра')

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
    <h1 class="text-2xl font-bold tracking-tight text-[#0e2442]">Угадай, кто это</h1>
    <p class="mt-1.5 text-sm text-gray-500">Угадывайте участников по их рассказам, копите очки и открывайте новые уровни.</p>
</div>

<div id="game-app" data-locked="{{ $locked }}" data-api="{{ json_encode($api) }}"></div>
</div>
@endsection
