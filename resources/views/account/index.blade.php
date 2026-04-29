@extends('account.layout')
@section('title', 'Главная')

@section('content')
<div class="max-w-3xl">
    <h1 class="text-2xl font-semibold text-gray-900 mb-1">
        Привет, {{ explode(' ', $accountUser->full_name)[0] }}! 👋
    </h1>
    <p class="text-gray-500 text-sm mb-8">Добро пожаловать в твой личный кабинет INSPIRE Community.</p>

    {{-- Quick links --}}
    <div class="grid sm:grid-cols-3 gap-4 mb-8">
        <a href="{{ route('account.profile') }}"
           class="group flex flex-col gap-3 bg-white border border-gray-100 rounded-2xl p-5 hover:border-brand-200 hover:shadow-sm transition">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center"
                 style="background:linear-gradient(135deg,#7c3aed,#4f46e5)">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </div>
            <div>
                <p class="font-medium text-gray-900 text-sm group-hover:text-brand-600 transition">Мой профиль</p>
                <p class="text-xs text-gray-400 mt-0.5">Обновить данные о себе</p>
            </div>
        </a>
        <a href="{{ route('account.matches') }}"
           class="group flex flex-col gap-3 bg-white border border-gray-100 rounded-2xl p-5 hover:border-brand-200 hover:shadow-sm transition">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-brand-50">
                <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <div>
                <p class="font-medium text-gray-900 text-sm group-hover:text-brand-600 transition">AI-матчи</p>
                <p class="text-xs text-gray-400 mt-0.5">Рекомендованные партнёры</p>
            </div>
        </a>
        <a href="{{ route('account.people') }}"
           class="group flex flex-col gap-3 bg-white border border-gray-100 rounded-2xl p-5 hover:border-brand-200 hover:shadow-sm transition">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-brand-50">
                <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </div>
            <div>
                <p class="font-medium text-gray-900 text-sm group-hover:text-brand-600 transition">Люди</p>
                <p class="text-xs text-gray-400 mt-0.5">Участники сообщества</p>
            </div>
        </a>
    </div>

    {{-- Profile completeness hint --}}
    @if(empty($accountUser->description))
    <div class="bg-amber-50 border border-amber-200 rounded-2xl px-5 py-4 flex items-start gap-3 text-sm">
        <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <div>
            <p class="font-medium text-amber-800">Заполни профиль</p>
            <p class="text-amber-700 mt-0.5">Расскажи о себе, чтобы алгоритм мог подобрать подходящих партнёров.</p>
            <a href="{{ route('account.profile') }}" class="inline-flex items-center gap-1 mt-2 font-medium text-amber-700 hover:text-amber-900 transition">
                Заполнить профиль
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
    </div>
    @endif
</div>
@endsection
