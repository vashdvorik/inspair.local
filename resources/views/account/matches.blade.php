@extends('account.layout')
@section('title', 'AI-матчи')

@section('content')
<div class="max-w-2xl">
    <h1 class="text-2xl font-semibold text-gray-900 mb-2">AI-матчи 🤝</h1>
    <p class="text-sm text-gray-500 mb-8">Алгоритм подберёт участников с наибольшим потенциалом для партнёрства на основе вашего профиля.</p>

    <div class="bg-white border border-brand-100 rounded-2xl p-8 text-center">
        <div class="w-14 h-14 rounded-2xl mx-auto mb-4 flex items-center justify-center"
             style="background:linear-gradient(135deg,#7c3aed,#4f46e5)">
            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        </div>
        <h2 class="font-semibold text-gray-900 mb-2">В разработке</h2>
        <p class="text-sm text-gray-500 max-w-sm mx-auto">
            AI-нетворкинг запустится в ближайшее время. Убедитесь, что ваш профиль заполнен — это улучшит качество рекомендаций.
        </p>
        @if(empty($accountUser->description))
        <a href="{{ route('account.profile') }}"
           class="inline-flex items-center gap-2 mt-5 px-4 py-2 text-sm font-semibold text-white rounded-xl"
           style="background:linear-gradient(135deg,#7c3aed,#4f46e5)">
            Заполнить профиль
        </a>
        @endif
    </div>
</div>
@endsection
