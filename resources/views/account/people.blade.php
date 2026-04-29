@extends('account.layout')
@section('title', 'Люди')

@section('content')
<div class="max-w-3xl">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Люди</h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ $people->count() }} участников сообщества</p>
        </div>
    </div>

    @if($people->isEmpty())
    <div class="bg-white border border-gray-100 rounded-2xl p-8 text-center text-sm text-gray-400">
        Пока нет других участников.
    </div>
    @else
    <div class="grid sm:grid-cols-2 gap-4">
        @foreach($people as $person)
        <div class="bg-white border border-gray-100 rounded-2xl p-5 hover:border-brand-200 hover:shadow-sm transition">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-full flex items-center justify-center text-white text-sm font-semibold flex-shrink-0"
                     style="background:linear-gradient(135deg,#7c3aed,#4f46e5)">
                    {{ mb_strtoupper(mb_substr($person->full_name ?? '?', 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <p class="font-medium text-gray-900 text-sm truncate">{{ $person->full_name }}</p>
                    @if($person->telegram_username)
                    <a href="https://t.me/{{ $person->telegram_username }}" target="_blank"
                       class="text-xs text-brand-600 hover:underline">@{{ $person->telegram_username }}</a>
                    @endif
                </div>
            </div>
            @if($person->description)
            <p class="text-xs text-gray-500 leading-relaxed line-clamp-3">{{ $person->description }}</p>
            @else
            <p class="text-xs text-gray-300 italic">Профиль не заполнен</p>
            @endif
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection
