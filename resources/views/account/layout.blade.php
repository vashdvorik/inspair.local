<!DOCTYPE html>
<html lang="ru" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Кабинет') — INSPIRE</title>
    <meta name="robots" content="noindex,nofollow">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Cormorant+Garamond:wght@500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans:  ['Inter', 'system-ui', 'sans-serif'],
                        serif: ['Cormorant Garamond', 'Georgia', 'serif'],
                    },
                    colors: {
                        brand: {
                            50:  '#f5f3ff',
                            100: '#ede9fe',
                            200: '#ddd6fe',
                            300: '#c4b5fd',
                            400: '#a78bfa',
                            500: '#8b5cf6',
                            600: '#7c3aed',
                            700: '#6d28d9',
                        },
                    },
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        :focus-visible { outline: 2px solid #7c3aed; outline-offset: 2px; border-radius: 6px; }
        ::-webkit-scrollbar { width: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(124,58,237,.2); border-radius: 4px; }
    </style>
    @stack('head')
</head>
<body class="h-full bg-[#f7f8fa] text-[#0f172a] antialiased" x-data="{ sidebarOpen: false }">

    {{-- Mobile overlay --}}
    <div x-show="sidebarOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-20 bg-black/50 backdrop-blur-sm lg:hidden"
         @click="sidebarOpen = false"></div>

    <div class="flex h-full">

        {{-- ── Sidebar ── --}}
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
               class="fixed inset-y-0 left-0 z-30 flex w-64 shrink-0 flex-col bg-white
                      transition-transform duration-200 ease-out
                      lg:static lg:translate-x-0 lg:z-auto"
               style="box-shadow: 1px 0 0 rgba(0,0,0,.06), 4px 0 24px rgba(0,0,0,.03)">

            {{-- Logo --}}
            <div class="flex shrink-0 items-center gap-3 px-5 pb-5 pt-6">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl"
                      style="background:linear-gradient(135deg,#7c3aed,#4f46e5);box-shadow:0 4px 12px rgba(124,58,237,.3)">
                    <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </span>
                <div class="leading-none">
                    <p class="text-sm font-bold tracking-tight text-[#0f172a]">INSPIRE</p>
                    <p class="mt-0.5 text-[10px] font-medium uppercase tracking-widest text-gray-400">Community</p>
                </div>
            </div>

            {{-- User card --}}
            <div class="mx-3 mb-5 shrink-0 rounded-2xl p-4"
                 style="background:linear-gradient(135deg,#f5f3ff,#ede9fe)">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-sm font-bold text-white"
                         style="background:linear-gradient(135deg,#7c3aed,#4f46e5);box-shadow:0 2px 8px rgba(124,58,237,.3)">
                        {{ mb_strtoupper(mb_substr($accountUser->full_name ?? '?', 0, 1)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold text-[#0f172a]">
                            {{ explode(' ', (string) $accountUser->full_name)[0] }}
                        </p>
                        @if($accountUser->telegram_username)
                        <p class="mt-0.5 truncate text-xs font-medium text-brand-600">
                            @{{ $accountUser->telegram_username }}
                        </p>
                        @endif
                    </div>
                    <span class="h-2 w-2 shrink-0 rounded-full bg-emerald-400"
                          style="box-shadow:0 0 0 3px rgba(52,211,153,.2)"></span>
                </div>
            </div>

            {{-- Navigation --}}
            <nav class="flex-1 overflow-y-auto px-3">
                <p class="mb-2 px-3 text-[10px] font-semibold uppercase tracking-widest text-gray-400">Навигация</p>
                @php
                    $navItems = [
                        ['route' => 'account.index',     'label' => 'Главная',
                         'path'  => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                        ['route' => 'account.matches',   'label' => 'AI-матчи',
                         'path'  => 'M13 10V3L4 14h7v7l9-11h-7z'],
                        ['route' => 'account.people',    'label' => 'Люди',
                         'path'  => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
                        ['route' => 'account.knowledge', 'label' => 'База знаний',
                         'path'  => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
                        ['route' => 'account.profile',   'label' => 'Мой профиль',
                         'path'  => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
                    ];
                @endphp
                @foreach($navItems as $item)
                @php $active = request()->routeIs($item['route']); @endphp
                <a href="{{ route($item['route']) }}"
                   @click="sidebarOpen = false"
                   class="group mb-0.5 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition-all duration-150
                          {{ $active
                             ? 'bg-brand-50 font-semibold text-brand-700'
                             : 'font-medium text-gray-500 hover:bg-gray-50 hover:text-gray-900' }}">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg transition-all duration-150
                                 {{ $active ? 'text-white' : 'bg-gray-100 text-gray-400 group-hover:bg-gray-200 group-hover:text-gray-600' }}"
                          @if($active) style="background:linear-gradient(135deg,#7c3aed,#4f46e5);box-shadow:0 2px 6px rgba(124,58,237,.25)" @endif>
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['path'] }}"/>
                        </svg>
                    </span>
                    <span class="flex-1">{{ $item['label'] }}</span>
                    @if($active)
                    <svg class="h-3 w-3 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                    </svg>
                    @endif
                </a>
                @endforeach
            </nav>

            {{-- Logout --}}
            <div class="shrink-0 border-t border-gray-100 px-3 py-4">
                <form action="{{ route('account.logout') }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="group flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium
                               text-gray-400 transition-all duration-150 hover:bg-red-50 hover:text-red-600">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gray-100
                                     transition-colors group-hover:bg-red-100">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                        </span>
                        Выйти
                    </button>
                </form>
            </div>

        </aside>

        {{-- ── Main area ── --}}
        <div class="flex min-w-0 flex-1 flex-col">

            {{-- Mobile header --}}
            <header class="sticky top-0 z-10 flex h-14 shrink-0 items-center gap-3 border-b border-gray-100 bg-white px-4 lg:hidden">
                <button @click="sidebarOpen = true"
                    class="flex h-9 w-9 items-center justify-center rounded-xl text-gray-500 hover:bg-gray-100 transition"
                    aria-label="Открыть меню">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <span class="text-sm font-semibold text-[#0f172a]">@yield('title', 'Кабинет')</span>
            </header>

            {{-- Page content --}}
            <main class="flex-1 overflow-y-auto px-6 py-8 lg:px-10">
                @if(session('success'))
                <div class="mb-6 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    <svg class="h-4 w-4 shrink-0 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ session('success') }}
                </div>
                @endif
                @if(session('error'))
                <div class="mb-6 flex items-center gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <svg class="h-4 w-4 shrink-0 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ session('error') }}
                </div>
                @endif
                @yield('content')
            </main>

        </div>
    </div>

</body>
</html>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Кабинет') — INSPIRE Community</title>
    <meta name="robots" content="noindex,nofollow">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Cormorant+Garamond:wght@500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans:  ['Inter', 'system-ui', 'sans-serif'],
                        serif: ['Cormorant Garamond', 'Georgia', 'serif'],
                    },
                    colors: {
                        brand: {
                            50: '#f5f3ff', 100: '#ede9fe', 200: '#ddd6fe',
                            400: '#a78bfa', 500: '#8b5cf6', 600: '#7c3aed', 700: '#6d28d9',
                        },
                    },
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        :focus-visible { outline: 2px solid #7c3aed; outline-offset: 2px; border-radius: 4px; }
        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-thumb { background: rgba(124,58,237,.25); border-radius: 4px; }
    </style>
    @stack('head')
</head>
<body class="h-full bg-gray-50 text-gray-900 antialiased" x-data="{ sidebarOpen: false }">

    {{-- Mobile overlay --}}
    <div x-show="sidebarOpen" x-transition.opacity
         class="fixed inset-0 bg-black/40 z-20 lg:hidden"
         @click="sidebarOpen = false"></div>

    {{-- Sidebar --}}
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
           class="fixed inset-y-0 left-0 z-30 w-64 bg-white border-r border-gray-100 flex flex-col transition-transform duration-200 lg:translate-x-0">

        {{-- Logo --}}
        <div class="flex items-center gap-3 px-5 h-16 border-b border-gray-100 flex-shrink-0">
            <span class="w-8 h-8 rounded-xl flex items-center justify-center shadow"
                  style="background:linear-gradient(135deg,#7c3aed,#4f46e5)">
                <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </span>
            <span class="font-semibold text-sm text-gray-900 tracking-tight">INSPIRE Community</span>
        </div>

        {{-- User info --}}
        <div class="px-4 py-4 border-b border-gray-100 flex-shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-sm font-semibold flex-shrink-0"
                     style="background:linear-gradient(135deg,#7c3aed,#4f46e5)">
                    {{ mb_strtoupper(mb_substr($accountUser->full_name ?? '?', 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">{{ $accountUser->full_name }}</p>
                    @if($accountUser->telegram_username)
                    <p class="text-xs text-gray-400 truncate">@{{ $accountUser->telegram_username }}</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Nav --}}
        <nav class="flex-1 px-3 py-3 space-y-0.5 overflow-y-auto">
            @php
                $navItems = [
                    ['route' => 'account.index',     'icon' => 'home',      'label' => 'Главная'],
                    ['route' => 'account.matches',   'icon' => 'match',     'label' => 'Матчи'],
                    ['route' => 'account.people',    'icon' => 'people',    'label' => 'Люди'],
                    ['route' => 'account.knowledge', 'icon' => 'knowledge', 'label' => 'База знаний'],
                    ['route' => 'account.profile',   'icon' => 'profile',   'label' => 'Мой профиль'],
                ];
            @endphp
            @foreach($navItems as $item)
            @php $active = request()->routeIs($item['route']); @endphp
            <a href="{{ route($item['route']) }}"
               @click="sidebarOpen = false"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors
                      {{ $active ? 'bg-brand-50 text-brand-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                @if($item['icon'] === 'home')
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                @elseif($item['icon'] === 'match')
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                @elseif($item['icon'] === 'people')
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                @elseif($item['icon'] === 'knowledge')
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                @else
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                @endif
                {{ $item['label'] }}
                @if($active)
                <span class="ml-auto w-1.5 h-1.5 rounded-full bg-brand-600"></span>
                @endif
            </a>
            @endforeach
        </nav>

        {{-- Logout --}}
        <div class="px-3 py-3 border-t border-gray-100 flex-shrink-0">
            <form action="{{ route('account.logout') }}" method="POST">
                @csrf
                <button type="submit"
                    class="flex items-center gap-3 w-full px-3 py-2.5 rounded-xl text-sm font-medium text-gray-500 hover:bg-red-50 hover:text-red-600 transition-colors">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Выйти
                </button>
            </form>
        </div>
    </aside>

    {{-- Main area --}}
    <div class="lg:pl-64 min-h-full flex flex-col">

        {{-- Top bar (mobile) --}}
        <header class="lg:hidden sticky top-0 z-10 flex items-center gap-3 px-4 h-14 bg-white border-b border-gray-100">
            <button @click="sidebarOpen = true"
                class="p-1.5 rounded-lg text-gray-500 hover:bg-gray-100 transition"
                aria-label="Открыть меню">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <span class="font-semibold text-sm text-gray-900">@yield('title', 'Кабинет')</span>
        </header>

        {{-- Page content --}}
        <main class="flex-1 px-4 sm:px-6 lg:px-8 py-8">
            @if(session('success'))
            <div class="mb-6 flex items-center gap-3 bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-xl">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ session('success') }}
            </div>
            @endif
            @if(session('error'))
            <div class="mb-6 flex items-center gap-3 bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-xl">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ session('error') }}
            </div>
            @endif
            @yield('content')
        </main>
    </div>

</body>
</html>
