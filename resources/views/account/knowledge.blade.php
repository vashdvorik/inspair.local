@extends('account.layout')
@section('title', 'База знаний')

@section('content')
<div class="max-w-2xl">
    <h1 class="text-2xl font-semibold text-gray-900 mb-2">База знаний 📚</h1>
    <p class="text-sm text-gray-500 mb-8">Ресурсы и материалы для участников сообщества.</p>

    <div class="space-y-3">
        {{-- Community chat --}}
        <a href="https://t.me/Inspiremoldova_bot" target="_blank"
           class="flex items-center gap-4 bg-white border border-gray-100 rounded-2xl px-5 py-4 hover:border-brand-200 hover:shadow-sm transition group">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                 style="background:linear-gradient(135deg,#7c3aed,#4f46e5)">
                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="font-medium text-gray-900 text-sm group-hover:text-brand-600 transition">Telegram-бот сообщества</p>
                <p class="text-xs text-gray-400 truncate">@Inspiremoldova_bot</p>
            </div>
            <svg class="w-4 h-4 text-gray-300 group-hover:text-brand-500 transition flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </a>

        {{-- INSPIRE website --}}
        <a href="https://peace.education.md/" target="_blank"
           class="flex items-center gap-4 bg-white border border-gray-100 rounded-2xl px-5 py-4 hover:border-brand-200 hover:shadow-sm transition group">
            <div class="w-10 h-10 rounded-xl bg-brand-50 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9"/></svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="font-medium text-gray-900 text-sm group-hover:text-brand-600 transition">Сайт проекта INSPIRE</p>
                <p class="text-xs text-gray-400 truncate">peace.education.md</p>
            </div>
            <svg class="w-4 h-4 text-gray-300 group-hover:text-brand-500 transition flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </a>

        {{-- Materials coming soon --}}
        <div class="flex items-center gap-4 bg-gray-50 border border-dashed border-gray-200 rounded-2xl px-5 py-4">
            <div class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            </div>
            <div>
                <p class="font-medium text-gray-400 text-sm">Обучающие материалы</p>
                <p class="text-xs text-gray-400">Скоро появятся</p>
            </div>
        </div>
    </div>
</div>
@endsection
