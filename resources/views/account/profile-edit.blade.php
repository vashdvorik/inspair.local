@extends('account.layout')
@section('title', $tr('Редактировать профиль', 'Edit profile', 'Editează profilul'))

@section('content')
<div class="cab-page">

    {{-- Header --}}
    <div class="mb-8 flex items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-[#0e2442]">{{ $tr('Редактировать профиль', 'Edit profile', 'Editează profilul') }}</h1>
            <p class="mt-1.5 text-sm text-gray-500">{{ $tr('Изменения сразу видны другим участникам', 'Changes are visible to other participants right away', 'Modificările sunt vizibile imediat pentru ceilalți participanți') }}</p>
        </div>
        <a href="{{ route('account.profile') }}"
           class="inline-flex h-10 shrink-0 items-center gap-2 rounded-xl border border-gray-200 bg-white px-5
                  text-sm font-semibold text-gray-500 shadow-sm transition hover:border-gray-300 hover:text-gray-700">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            {{ $tr('Назад', 'Back', 'Înapoi') }}
        </a>
    </div>

    <form action="{{ route('account.profile.update') }}" method="POST" class="space-y-5">
        @csrf

        {{-- Identity --}}
        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <h2 class="mb-5 text-xs font-semibold uppercase tracking-widest text-gray-400">{{ $tr('Личные данные', 'Personal details', 'Date personale') }}</h2>
            <div class="space-y-5">

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-[#0e2442]">
                        {{ $tr('Имя и фамилия', 'Full name', 'Nume și prenume') }} <span class="text-red-400">*</span>
                    </label>
                    <input type="text" name="full_name"
                           value="{{ old('full_name', $accountUser->full_name) }}"
                           maxlength="120" required
                           placeholder="{{ $tr('Иван Иванов', 'John Smith', 'Ion Popescu') }}"
                           class="w-full rounded-xl border px-4 py-3 text-sm text-[#0e2442] placeholder-gray-300
                                  transition duration-150 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent
                                  {{ $errors->has('full_name') ? 'border-red-300 bg-red-50' : 'border-gray-200 bg-gray-50 focus:bg-white' }}">
                    @error('full_name')
                    <p class="mt-1.5 flex items-center gap-1 text-xs text-red-600">
                        <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ $message }}
                    </p>
                    @enderror
                </div>

                <div>
                    <label for="organization" class="mb-1.5 block text-sm font-medium text-[#0e2442]">
                        {{ $tr('Университет/организация и должность', 'University/organization and position', 'Universitate/organizație și funcție') }}
                    </label>
                    <input id="organization" type="text" name="organization"
                           value="{{ old('organization', $accountUser->organization) }}"
                           maxlength="160"
                           class="w-full rounded-xl border px-4 py-3 text-sm text-[#0e2442] placeholder-gray-300
                                  transition duration-150 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent
                                  {{ $errors->has('organization') ? 'border-red-300 bg-red-50' : 'border-gray-200 bg-gray-50 focus:bg-white' }}">
                    @error('organization')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="city" class="mb-1.5 block text-sm font-medium text-[#0e2442]">{{ $tr('Город', 'City', 'Oraș') }}</label>
                    <input id="city" type="text" name="city" value="{{ old('city', $accountUser->city) }}" maxlength="80" autocomplete="address-level2"
                           class="w-full rounded-xl border px-4 py-3 text-sm text-[#0e2442] placeholder-gray-300
                                  transition duration-150 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent
                                  {{ $errors->has('city') ? 'border-red-300 bg-red-50' : 'border-gray-200 bg-gray-50 focus:bg-white' }}">
                    @error('city')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="rounded-xl border border-gray-100 bg-gray-50 px-4 py-3">
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

        {{-- About --}}
        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <h2 class="mb-5 text-xs font-semibold uppercase tracking-widest text-gray-400">{{ $tr('О себе', 'About me', 'Despre mine') }}</h2>
            <div class="space-y-5">

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-[#0e2442]">
                        {{ $tr('Кто ты и чем занимаешься?', 'Who are you and what do you do?', 'Cine ești și cu ce te ocupi?') }}
                    </label>
                    <textarea name="description" rows="4" maxlength="1000"
                              placeholder="{{ $tr('Роль, сфера, компания. Ссылки приветствуются.', 'Role, field, company. Links are welcome.', 'Rol, domeniu, companie. Link-urile sunt binevenite.') }}"
                              class="w-full resize-none rounded-xl border px-4 py-3 text-sm text-[#0e2442] placeholder-gray-300
                                     transition duration-150 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent
                                     {{ $errors->has('description') ? 'border-red-300 bg-red-50' : 'border-gray-200 bg-gray-50 focus:bg-white' }}">{{ old('description', $accountUser->description) }}</textarea>
                    @error('description')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

            </div>
        </div>


        {{-- Contacts: shown to approved participants unless the profile hides them --}}
        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <h2 class="mb-5 text-xs font-semibold uppercase tracking-widest text-gray-400">{{ $tr('Контакты', 'Contacts', 'Contacte') }}</h2>
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="contact_phone" class="mb-1.5 block text-sm font-medium text-[#0e2442]">{{ $tr('Телефон', 'Phone', 'Telefon') }}</label>
                    <input id="contact_phone" type="text" inputmode="tel" name="contact_phone" value="{{ old('contact_phone', $accountUser->contact_phone) }}" maxlength="30" autocomplete="tel" placeholder="+373 600 00000"
                           class="w-full rounded-xl border px-4 py-3 text-sm text-[#0e2442] placeholder-gray-300
                                  transition duration-150 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent
                                  {{ $errors->has('contact_phone') ? 'border-red-300 bg-red-50' : 'border-gray-200 bg-gray-50 focus:bg-white' }}">
                    @error('contact_phone')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="contact_email" class="mb-1.5 block text-sm font-medium text-[#0e2442]">{{ $tr('Почта', 'Email', 'E-mail') }}</label>
                    <input id="contact_email" type="text" inputmode="email" name="contact_email" value="{{ old('contact_email', $accountUser->contact_email) }}" maxlength="120" autocomplete="email" placeholder="name@example.com"
                           class="w-full rounded-xl border px-4 py-3 text-sm text-[#0e2442] placeholder-gray-300
                                  transition duration-150 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent
                                  {{ $errors->has('contact_email') ? 'border-red-300 bg-red-50' : 'border-gray-200 bg-gray-50 focus:bg-white' }}">
                    @error('contact_email')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- Privacy --}}
        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <h2 class="mb-5 text-xs font-semibold uppercase tracking-widest text-gray-400">{{ $tr('Приватность', 'Privacy', 'Confidențialitate') }}</h2>
            <div class="space-y-4">
                <label class="flex items-start gap-3 text-sm text-[#0e2442]">
                    <input type="checkbox" name="contacts_closed" value="1" class="mt-0.5 h-4 w-4 rounded border-gray-300" {{ old('contacts_closed', $accountUser->contacts_closed) ? 'checked' : '' }}>
                    <span><b>{{ $tr('Закрыть мои контакты', 'Hide my contacts', 'Ascunde contactele mele') }}</b><br><span class="text-xs text-gray-500">{{ $tr('Мой Telegram и кнопка «Написать» не показываются другим участникам. Я по-прежнему могу писать им сам.', 'My Telegram and the “Message” button are not shown to other participants. I can still write to them myself.', 'Telegramul meu și butonul „Scrie” nu sunt afișate celorlalți participanți. Eu pot în continuare să le scriu.') }}</span></span>
                </label>
                <label class="flex items-start gap-3 text-sm text-[#0e2442]">
                    <input type="hidden" name="notifications" value="0">
                    <input type="checkbox" name="notifications" value="1" class="mt-0.5 h-4 w-4 rounded border-gray-300" {{ old('notifications', ! $accountUser->notifications_muted) ? 'checked' : '' }}>
                    <span><b>{{ $tr('Получать новости и возможности в боте', 'Get news and opportunities in the bot', 'Primește știri și oportunități în bot') }}</b><br><span class="text-xs text-gray-500">{{ $tr('Бот пришлёт сообщение, когда на сайте появится новая возможность: курс, программа или поездка за рубежом.', 'The bot will message you when a new opportunity appears on the site: a course, a programme or a trip abroad.', 'Botul îți va scrie când pe site apare o oportunitate nouă: un curs, un program sau o călătorie în străinătate.') }}</span></span>
                </label>
                @if($game->isEnabled())
                <label class="flex items-start gap-3 text-sm text-[#0e2442]">
                    <input type="checkbox" name="quests_opt_out" value="1" class="mt-0.5 h-4 w-4 rounded border-gray-300" {{ old('quests_opt_out', $accountUser->quests_opt_out) ? 'checked' : '' }}>
                    <span><b>{{ $tr('Не использовать меня в квестах', 'Don’t use me in questions', 'Nu mă folosi în întrebări') }}</b><br><span class="text-xs text-gray-500">{{ $tr('Мои факты не попадут в загадки, и другие не смогут угадывать меня.', 'My facts won’t appear in riddles, and others won’t be able to guess me.', 'Faptele mele nu vor apărea în ghicitori, iar ceilalți nu mă vor putea ghici.') }}</span></span>
                </label>
                @endif
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-between">
            <button type="submit"
                class="inline-flex h-11 items-center gap-2 rounded-xl px-6 text-sm font-semibold text-white
                       transition-all duration-150 hover:-translate-y-px hover:shadow-lg active:translate-y-0"
                style="background:var(--grad-primary);box-shadow:0 4px 14px rgba(0,109,224,.3)">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                {{ $tr('Сохранить изменения', 'Save changes', 'Salvează modificările') }}
            </button>
            
        </div>

    </form>

</div>
@endsection
