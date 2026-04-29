@extends('account.layout')
@section('title', 'Мой профиль')

@section('content')
<div class="max-w-2xl">
    <h1 class="text-2xl font-semibold text-gray-900 mb-6">Мой профиль</h1>

    <form action="{{ route('account.profile.update') }}" method="POST" class="bg-white border border-gray-100 rounded-2xl p-6 space-y-5">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Имя и фамилия <span class="text-red-500">*</span></label>
            <input type="text" name="full_name" value="{{ old('full_name', $accountUser->full_name) }}"
                   maxlength="120" required
                   class="w-full px-3.5 py-2.5 text-sm border rounded-xl transition focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent
                          {{ $errors->has('full_name') ? 'border-red-300 bg-red-50' : 'border-gray-200 bg-gray-50 focus:bg-white' }}">
            @error('full_name')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Кто ты и чем занимаешься?</label>
            <textarea name="description" rows="4" maxlength="1000"
                      placeholder="Роль, сфера, компания. Ссылки приветствуются."
                      class="w-full px-3.5 py-2.5 text-sm border rounded-xl transition resize-none focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent
                             {{ $errors->has('description') ? 'border-red-300 bg-red-50' : 'border-gray-200 bg-gray-50 focus:bg-white' }}">{{ old('description', $accountUser->description) }}</textarea>
            @error('description')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Что ждёшь от сообщества?</label>
            <textarea name="expectation" rows="3" maxlength="1000"
                      placeholder="Чего ищешь и чем можешь быть полезен."
                      class="w-full px-3.5 py-2.5 text-sm border rounded-xl transition resize-none focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent
                             {{ $errors->has('expectation') ? 'border-red-300 bg-red-50' : 'border-gray-200 bg-gray-50 focus:bg-white' }}">{{ old('expectation', $accountUser->expectation) }}</textarea>
            @error('expectation')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center gap-4 pt-1 border-t border-gray-100">
            <button type="submit"
                class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white rounded-xl transition hover:-translate-y-px hover:shadow-md"
                style="background:linear-gradient(135deg,#7c3aed,#4f46e5)">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Сохранить
            </button>
            <p class="text-xs text-gray-400">Telegram: @{{ $accountUser->telegram_username ?? '—' }}</p>
        </div>
    </form>
</div>
@endsection
