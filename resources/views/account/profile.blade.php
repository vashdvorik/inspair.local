@extends('account.layout')
@section('title', $tr('Мой профиль', 'My profile', 'Profilul meu'))

@section('content')
<div class="cab-page">

    {{-- Header --}}
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="flex items-center gap-4">
            @if($accountUser->avatar_path)
            <img src="{{ $accountUser->avatarUrl() }}"
                 alt="{{ $accountUser->full_name }}"
                 class="h-16 w-16 shrink-0 rounded-2xl object-cover shadow-md">
            @else
            <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl text-xl font-bold text-white shadow-md"
                 style="background:var(--grad-primary)">
                {{ mb_strtoupper(mb_substr($accountUser->full_name ?? '?', 0, 1)) }}
            </div>
            @endif
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-[#0e2442]">{{ $tr('Мой профиль', 'My profile', 'Profilul meu') }}</h1>
                <p class="mt-1 text-sm text-gray-500">{{ $tr('Информация о тебе, видимая участникам сообщества', 'Information about you that community members can see', 'Informații despre tine, vizibile membrilor comunității') }}</p>
            </div>
        </div>
        <a href="{{ route('account.profile.edit') }}"
           class="inline-flex h-10 items-center gap-2 rounded-xl border border-gray-200 bg-white px-5
                  text-sm font-semibold text-[#0e2442] shadow-sm transition hover:border-brand-400 hover:text-brand-600
                  sm:shrink-0">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 112.828 2.828L11.828 15.828A2 2 0 0110 16H8v-2a2 2 0 01.586-1.414z"/>
            </svg>
            {{ $tr('Редактировать', 'Edit', 'Editează') }}
        </a>
    </div>

    @if(session('success'))
    <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
        {{ session('success') }}
    </div>
    @endif

    {{-- Identity --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <h2 class="mb-5 text-xs font-semibold uppercase tracking-widest text-gray-400">{{ $tr('Личные данные', 'Personal details', 'Date personale') }}</h2>
        <div class="space-y-4">

            <div>
                <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-400">{{ $tr('Имя и фамилия', 'Full name', 'Nume și prenume') }}</p>
                <p class="mt-1 text-sm font-medium text-[#0e2442]">
                    {{ $accountUser->full_name ?: '—' }}
                </p>
            </div>

            <div>
                <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-400">{{ $tr('Университет/организация и должность', 'University/organization and position', 'Universitate/organizație și funcție') }}</p>
                <p class="mt-1 text-sm font-medium text-[#0e2442]">{{ $accountUser->organization ?: '—' }}</p>
            </div>

            <div>
                <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-400">{{ $tr('Город', 'City', 'Oraș') }}</p>
                <p class="mt-1 text-sm font-medium text-[#0e2442]">{{ $accountUser->city ?: '—' }}</p>
            </div>

            <div>
                <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-400">Telegram</p>
                <p class="mt-1 text-sm font-medium">
                    @if($accountUser->telegram_username)
                    <a href="https://t.me/{{ $accountUser->telegram_username }}" target="_blank"
                       class="text-brand-600 hover:underline">
                        {{ '@' . $accountUser->telegram_username }}
                    </a>
                    @else
                    <span class="text-gray-400">{{ $tr('Не указан', 'Not set', 'Nespecificat') }}</span>
                    @endif
                </p>
            </div>

        </div>
    </div>

    {{-- Contacts --}}
    <div class="mt-5 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <h2 class="mb-5 text-xs font-semibold uppercase tracking-widest text-gray-400">{{ $tr('Контакты', 'Contacts', 'Contacte') }}</h2>
        <div class="space-y-4">
            <div>
                <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-400">{{ $tr('Телефон', 'Phone', 'Telefon') }}</p>
                <p class="mt-1 text-sm font-medium text-[#0e2442]">{{ $accountUser->contact_phone ?: '—' }}</p>
            </div>
            <div>
                <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-400">{{ $tr('Почта', 'Email', 'E-mail') }}</p>
                <p class="mt-1 text-sm font-medium text-[#0e2442]">{{ $accountUser->contact_email ?: '—' }}</p>
            </div>
        </div>
    </div>

    {{-- About --}}
    <div class="mt-5 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <h2 class="mb-5 text-xs font-semibold uppercase tracking-widest text-gray-400">{{ $tr('О себе', 'About me', 'Despre mine') }}</h2>
        <div class="space-y-5">

            <div>
                <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-400">{{ $tr('Кто ты и чем занимаешься?', 'Who are you and what do you do?', 'Cine ești și cu ce te ocupi?') }}</p>
                <p class="mt-1 whitespace-pre-line text-sm text-[#0e2442]">
                    {{ $accountUser->description ?: '—' }}
                </p>
            </div>

        </div>
    </div>

</div>

@endsection
