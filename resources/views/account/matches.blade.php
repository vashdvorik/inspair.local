@extends('account.layout')
@section('title', 'AI-матчи')

@section('content')
<div class="max-w-2xl">

    <div class="mb-8">
        <h1 class="text-2xl font-bold tracking-tight text-[#0f172a]">AI-матчи</h1>
        <p class="mt-1.5 text-sm text-gray-500">Рекомендованные партнёры на основе твоего профиля</p>
    </div>

    <div class="rounded-2xl border border-gray-100 bg-white p-10 text-center shadow-sm">
        <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-2xl"
             style="background:linear-gradient(135deg,#f5f3ff,#ede9fe)">
            <svg class="h-8 w-8 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
        </div>
        <p class="text-xs font-semibold uppercase tracking-widest text-brand-500">Скоро</p>
        <h2 class="mt-2 text-base font-semibold text-[#0f172a]">AI-нетворкинг в разработке</h2>
        <p class="mx-auto mt-2 max-w-sm text-sm leading-relaxed text-gray-500">
            Алгоритм подберёт участников с наибольшим потенциалом для партнёрства.
            Заполни профиль — это улучшит качество рекомендаций.
        </p>
        @if(empty($accountUser->description))
        <a href="{{ route('account.profile') }}"
           class="mt-6 inline-flex h-10 items-center gap-2 rounded-xl px-5 text-sm font-semibold text-white
                  transition-all hover:-translate-y-px hover:shadow-md"
           style="background:linear-gradient(135deg,#7c3aed,#4f46e5)">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            Заполнить профиль
        </a>
        @endif
    </div>

</div>
@endsection
