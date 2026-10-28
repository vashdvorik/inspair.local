<!DOCTYPE html>
<html lang="ru" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Кабинет') — INSPIRE EDU Network</title>
    <meta name="robots" content="noindex,nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#ffffff">
    <link rel="icon" href="{{ \App\Support\PublicUi::versioned('brand/favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ \App\Support\PublicUi::versioned('brand/favicon-32.png') }}">
    <link rel="apple-touch-icon" href="{{ \App\Support\PublicUi::versioned('brand/apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ \App\Support\PublicUi::versioned('site.webmanifest') }}">
    <link rel="stylesheet" href="{{ \App\Support\PublicUi::versioned('design/inspire.css') }}">
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
    <link rel="stylesheet" href="{{ \App\Support\PublicUi::versioned('design/tailwind.css') }}">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        body { background: var(--bg-soft); }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--blue-200); border-radius: 6px; }

        /* soft light backdrop behind the content (no dark areas anywhere) */
        .app-bg { position: fixed; inset: 0; z-index: -1; pointer-events: none; overflow: hidden;
                  background: linear-gradient(180deg, #ffffff 0%, var(--bg-soft) 45%, var(--bg-tint) 100%); }
        .app-bg::before, .app-bg::after { content: ""; position: absolute; border-radius: 50%; will-change: transform; }
        .app-bg::before { width: 620px; height: 620px; top: -240px; right: -120px; background: radial-gradient(circle, rgba(1,158,255,.16), rgba(1,158,255,0) 68%); animation: i-drift-a 26s ease-in-out infinite; }
        .app-bg::after  { width: 520px; height: 520px; bottom: -240px; left: 240px; background: radial-gradient(circle, rgba(253,186,7,.14), rgba(253,186,7,0) 68%); animation: i-drift-b 30s ease-in-out infinite; }

        /* sidebar */
        .side-link { display: flex; align-items: center; gap: .75rem; padding: .55rem .75rem; margin-bottom: 2px; border-radius: 14px; font-size: .875rem; font-weight: 600; color: var(--muted); text-decoration: none; transition: background var(--t-base), color var(--t-base), transform var(--t-base) var(--ease); }
        .side-link:hover { background: var(--blue-50); color: var(--blue-700); transform: translateX(3px); }
        .side-link.is-active { background: var(--blue-100); color: var(--navy-800); }
        .side-ico { width: 34px; height: 34px; flex: none; display: grid; place-items: center; border-radius: 11px; background: var(--bg-tint); color: var(--blue-600); transition: background var(--t-base), color var(--t-base), transform var(--t-base) var(--ease); }
        .side-link:hover .side-ico { transform: scale(1.08); }
        .side-link.is-active .side-ico { background: var(--grad-primary); color: #fff; box-shadow: var(--sh-primary); }
        .side-ico--yellow { background: var(--yellow-100); color: #b07d00; } .side-ico--orange { background: var(--orange-100); color: #c4500a; }
        .side-ico--green { background: var(--green-100); color: var(--green); } .side-ico--purple { background: var(--purple-100); color: var(--purple); } .side-ico--red { background: var(--red-100); color: var(--red); }

        /* gentle entrance + hover for page content */
        @keyframes app-in { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: none; } }
        main > * > * { animation: app-in .6s var(--ease) both; }
        main > * > *:nth-child(2) { animation-delay: .06s; } main > * > *:nth-child(3) { animation-delay: .12s; } main > * > *:nth-child(4) { animation-delay: .18s; } main > * > *:nth-child(5) { animation-delay: .24s; }
        main a.rounded-2xl, main a.rounded-xl { transition: transform var(--t-base) var(--ease), box-shadow var(--t-base) var(--ease), border-color var(--t-base); }
        main a.rounded-2xl:hover { transform: translateY(-3px); box-shadow: var(--sh-md); border-color: var(--blue-200); }
        main button, main a.rounded-xl { transition: transform var(--t-base) var(--ease), box-shadow var(--t-base) var(--ease), background var(--t-base); }
        main input:focus, main textarea:focus, main select:focus { outline: none; border-color: var(--blue-500) !important; box-shadow: var(--ring); }
        @media (prefers-reduced-motion: reduce) { main *, .side-link, .app-bg::before, .app-bg::after { animation: none !important; transition: none !important; } }
    </style>
    @stack('head')
</head>
<body class="i-body h-full text-ink antialiased" x-data="{ sidebarOpen: false }">
    <div class="app-bg" aria-hidden="true"></div>

    {{-- Mobile overlay (light) --}}
    <div x-show="sidebarOpen" x-cloak
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-20 bg-brand-100/70 lg:hidden" @click="sidebarOpen = false"></div>

    <div class="flex h-full">

        {{-- ── Sidebar ── --}}
        <aside x-cloak :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
               class="fixed inset-y-0 left-0 z-30 flex w-72 shrink-0 flex-col bg-white transition-transform duration-300 ease-out lg:static lg:z-auto lg:translate-x-0"
               style="border-right:1px solid var(--line);box-shadow:var(--sh-sm)">
            <div class="i-spectrum-line" style="border-radius:0;height:3px"></div>

            {{-- Logo --}}
            <a href="{{ route('account.index') }}" class="block shrink-0 px-6 pb-4 pt-5" aria-label="INSPIRE EDU Network">
                <img src="{{ \App\Support\PublicUi::versioned('brand/logo.png') }}" alt="INSPIRE EDU Network" width="120" height="61" style="height:58px;width:auto">
            </a>

            {{-- User card --}}
            <div class="mx-4 mb-5 shrink-0 rounded-2xl p-4" style="background:linear-gradient(135deg,var(--blue-50),var(--bg-tint));border:1px solid var(--blue-100)">
                <div class="flex items-center gap-3">
                    @if($accountUser->avatar_path)
                        <img src="{{ Storage::url($accountUser->avatar_path) }}" alt="{{ $accountUser->full_name }}" class="h-11 w-11 shrink-0 rounded-full object-cover" style="box-shadow:0 0 0 2px #fff,0 4px 12px rgba(0,109,224,.25)">
                    @else
                        <div class="i-avatar" style="box-shadow:0 0 0 2px #fff,0 4px 12px rgba(0,109,224,.25)">{{ mb_strtoupper(mb_substr($accountUser->full_name ?? '?', 0, 1)) }}</div>
                    @endif
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-bold text-ink">{{ explode(' ', (string) $accountUser->full_name)[0] }}</p>
                        @if($accountUser->telegram_username)
                            <p class="mt-0.5 truncate text-xs font-semibold text-brand-600">{{ '@' . $accountUser->telegram_username }}</p>
                        @endif
                    </div>
                    <span class="i-dot i-pulse-dot" title="online"></span>
                </div>
            </div>

            {{-- Navigation --}}
            <nav class="flex-1 overflow-y-auto px-4">
                <p class="i-eyebrow mb-2 px-3" style="color:var(--subtle);font-size:.66rem">Навигация</p>
                @php
                    $navItems = [
                        ['route' => 'account.index',     'label' => 'Главная',      'tone' => '',
                         'path'  => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                        ['route' => 'account.game',      'label' => 'Игра',         'tone' => 'orange',
                         'path'  => 'M13 10V3L4 14h7v7l9-11h-7z'],
                        ['route' => 'account.matches',   'label' => 'AI-матчи',     'tone' => 'yellow',
                         'path'  => 'M13 10V3L4 14h7v7l9-11h-7z'],
                        ['route' => 'account.search',    'label' => 'AI-поиск',     'tone' => 'purple',
                         'path'  => 'M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z'],
                        ['route' => 'account.people',    'label' => 'Люди',         'tone' => 'green',
                         'path'  => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
                        ['route' => 'account.opportunities.index', 'label' => 'Возможности', 'tone' => 'orange',
                         'path'  => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
                        ['route' => 'account.knowledge', 'label' => 'База знаний',  'tone' => '',
                         'path'  => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
                        ['route' => 'account.profile',   'label' => 'Мой профиль',  'tone' => 'red',
                         'path'  => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
                    ];
                @endphp
                @foreach($navItems as $item)
                    @continue($item['route'] === 'account.game' && ! $game->isEnabled())
                    @php $active = request()->routeIs($item['route']) || request()->routeIs($item['route'] . '.*'); @endphp
                    <a href="{{ route($item['route']) }}" @click="sidebarOpen = false" class="side-link {{ $active ? 'is-active' : '' }}">
                        <span class="side-ico {{ !$active && $item['tone'] ? 'side-ico--' . $item['tone'] : '' }}">
                            <svg class="h-[18px] w-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="{{ $item['path'] }}"/></svg>
                        </span>
                        <span class="flex-1">{{ $item['label'] }}</span>
                        @if($active)
                            <svg class="h-3.5 w-3.5 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                        @endif
                    </a>
                @endforeach
            </nav>

            {{-- Logout --}}
            <div class="shrink-0 px-4 py-4" style="border-top:1px solid var(--line)">
                <form action="{{ route('account.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="side-link w-full hover:!bg-red-50 hover:!text-red-600" style="background:none;border:0;cursor:pointer;font-family:inherit">
                        <span class="side-ico">
                            <svg class="h-[18px] w-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        </span>
                        Выйти
                    </button>
                </form>
            </div>
        </aside>

        {{-- ── Main area ── --}}
        <div class="flex min-w-0 flex-1 flex-col">

            {{-- Mobile header --}}
            <header class="sticky top-0 z-10 flex h-16 shrink-0 items-center gap-3 bg-white/95 px-4 lg:hidden" style="border-bottom:1px solid var(--line)">
                <button @click="sidebarOpen = true" class="flex h-10 w-10 items-center justify-center rounded-xl text-navy-800 transition hover:bg-brand-50" aria-label="Открыть меню">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <img src="{{ \App\Support\PublicUi::versioned('brand/logo-mark.png') }}" alt="" class="h-9 w-9">
                <span class="text-sm font-bold text-ink">@yield('title', 'Кабинет')</span>
            </header>

            {{-- Page content --}}
            <main class="flex-1 overflow-y-auto px-5 py-8 lg:px-10">
                @if(session('success'))
                    <div class="i-alert i-alert--success mb-6 items-center" role="status">
                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="i-alert i-alert--danger mb-6 items-center" role="alert">
                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ session('error') }}
                    </div>
                @endif
                @yield('content')
            </main>

        </div>
    </div>

<script>
    (function () {
        if (!window.Telegram || !window.Telegram.WebApp) return;
        var tg = window.Telegram.WebApp;

        // Signal the app is ready and expand to full height
        tg.ready();
        tg.expand();

        // Hide logout button inside Mini App — auto-auth makes it pointless
        var logoutForm = document.querySelector('form[action*="logout"]');
        if (logoutForm) logoutForm.closest('div').style.display = 'none';

        document.querySelectorAll('a[href*="t.me/Inspiremoldova_bot"]').forEach(function (link) {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                tg.openTelegramLink(link.href);
                tg.close();
            });
        });

        // After logout the server redirects to login with ?logout=1 — no need
        // to close the app, the redirect will show the logged-out state.
    })();
</script>
@stack('scripts')
</body>
</html>
