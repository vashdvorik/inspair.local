<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'INSPIRE EDU Network')</title>
    <meta name="description" content="@yield('description', 'INSPIRE EDU Network — платформа партнёрства и роста для молодых лидеров Молдовы: сообщество, Академия 2025 и Академия 2023.')">
    <meta name="theme-color" content="#ffffff">
    <meta property="og:title" content="@yield('title', 'INSPIRE EDU Network')">
    <meta property="og:description" content="@yield('description', 'Платформа партнёрства и роста для лидеров Молдовы')">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="INSPIRE EDU Network">
    <meta property="og:url" content="{{ url()->current() }}">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:image" content="{{ asset('img/hero/og.jpg') }}">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="icon" href="{{ \App\Support\PublicUi::versioned('brand/favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ \App\Support\PublicUi::versioned('brand/favicon-32.png') }}">
    <link rel="apple-touch-icon" href="{{ \App\Support\PublicUi::versioned('brand/apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ \App\Support\PublicUi::versioned('site.webmanifest') }}">
    <link rel="stylesheet" href="{{ \App\Support\PublicUi::versioned('design/inspire.css') }}">
    <link rel="stylesheet" href="{{ \App\Support\PublicUi::versioned('design/pages.css') }}">
    <link rel="stylesheet" href="{{ \App\Support\PublicUi::versioned('design/tailwind.css') }}">
    <script>
        // the Telegram WebApp script is only needed when the page is opened inside Telegram
        (function () {
            if ((location.hash || '').indexOf('tgWebApp') > -1 || window.TelegramWebviewProxy || /Telegram/i.test(navigator.userAgent)) {
                var s = document.createElement('script'); s.src = 'https://telegram.org/js/telegram-web-app.js'; document.head.appendChild(s);
            }
        })();
    </script>
    <script>
        (function () {
            var s = null; try { s = localStorage.getItem('inspair_lang'); } catch (e) {}
            var l = ['en', 'ru', 'ro'].indexOf(s) >= 0 ? s : 'en';
            document.documentElement.lang = l;
        })();
    </script>
    @stack('head')
</head>
<body class="i-body">

@php
    $menu = [
        ['route' => 'home', 'label' => ['Главная', 'Home', 'Acasă'], 'active' => ['home']],
        ['label' => ['Мероприятия', 'Events', 'Evenimente'], 'active' => ['hackathon', 'academy2025*', 'academy2023'], 'children' => [
            ['route' => 'hackathon',   'label' => ['Хакатон 2026', 'Hackathon 2026', 'Hackathon 2026']],
            ['route' => 'academy2025', 'label' => ['Школы 2025', 'Schools 2025', 'Școli 2025']],
            ['route' => 'academy2023', 'label' => ['Школа 2023', 'School 2023', 'Școala 2023']],
        ]],
        ['route' => 'experts',      'label' => ['Эксперты', 'Experts', 'Experți'],        'active' => ['experts']],
        ['route' => 'photo',        'label' => ['Фото', 'Photo', 'Foto'],                 'active' => ['photo']],
        ['route' => 'video',        'label' => ['Видео', 'Video', 'Video'],               'active' => ['video']],
        ['route' => 'partners',     'label' => ['Партнёры', 'Partners', 'Parteneri'],     'active' => ['partners']],
        ['route' => 'publications', 'label' => ['Публикации', 'Publications', 'Publicații'], 'active' => ['publications']],
    ];
    $isActive = fn (array $m) => request()->routeIs(...$m['active']);
@endphp

{{-- ───────────── NAVBAR ───────────── --}}
<nav id="navbar" class="i-nav" aria-label="Main">
    <div class="i-container i-nav__inner">
        <a href="{{ route('home') }}" class="i-nav__logo" aria-label="INSPIRE EDU Network">
            <img src="{{ \App\Support\PublicUi::versioned('brand/logo.png') }}" alt="INSPIRE EDU Network" width="90" height="46">
        </a>
        <div class="i-nav__links" style="margin-left:auto;">
            @foreach($menu as $m)
                @if(isset($m['children']))
                    <div class="nav-dd {{ $isActive($m) ? 'is-active' : '' }}">
                        <button type="button" class="nav-dd__btn {{ $isActive($m) ? 'is-active' : '' }}" aria-haspopup="true" aria-expanded="false">{!! $t(...$m['label']) !!}<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/></svg></button>
                        <div class="nav-dd__menu" role="menu">
                            @foreach($m['children'] as $ch)
                                <a role="menuitem" href="{{ route($ch['route']) }}" @class(['is-active' => request()->routeIs($ch['route'] . '*')])>{!! $t(...$ch['label']) !!}</a>
                            @endforeach
                        </div>
                    </div>
                @else
                    <a href="{{ route($m['route']) }}" @class(['is-active' => $isActive($m)])>{!! $t(...$m['label']) !!}</a>
                @endif
            @endforeach
        </div>
        <div class="flex items-center gap-3">
            <div class="i-lang" role="group" aria-label="Language">
                @foreach(['en','ru','ro'] as $l)
                    <button type="button" onclick="setLang('{{ $l }}')" id="btn-{{ $l }}" class="lang-btn">{{ strtoupper($l) }}</button>
                @endforeach
            </div>
            <a href="{{ route('account.login') }}" class="i-btn i-btn--sm hidden sm:inline-flex">{!! $tg !!}<span>{!! $t('Войти в кабинет', 'Sign in', 'Intră în cont') !!}</span></a>
            <button id="mobile-menu-btn" type="button" class="lg:hidden p-2 rounded-xl text-navy-800 hover:bg-brand-50 transition" aria-label="Menu" aria-expanded="false" aria-controls="mobile-menu">
                <svg id="hamburger-icon" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                <svg id="close-icon" class="w-6 h-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </div>
    <div id="mobile-menu" class="hidden lg:hidden border-t border-gray-200 shadow-i-md" style="background:#fff;max-height:calc(100vh - 72px);overflow-y:auto">
        <div class="i-container py-4 flex flex-col gap-1">
            @foreach($menu as $m)
                @if(isset($m['children']))
                    <p class="px-4 pt-3 pb-1 i-eyebrow" style="color:var(--muted)">{!! $t(...$m['label']) !!}</p>
                    @foreach($m['children'] as $ch)
                        <a href="{{ route($ch['route']) }}" class="mobile-nav-link px-4 py-3 pl-8 rounded-xl text-sm font-bold transition {{ request()->routeIs($ch['route'] . '*') ? 'bg-brand-100 text-navy-800' : 'text-gray-700 hover:bg-brand-50 hover:text-brand-700' }}">{!! $t(...$ch['label']) !!}</a>
                    @endforeach
                @else
                    <a href="{{ route($m['route']) }}" class="mobile-nav-link px-4 py-3 rounded-xl text-sm font-bold transition {{ $isActive($m) ? 'bg-brand-100 text-navy-800' : 'text-gray-700 hover:bg-brand-50 hover:text-brand-700' }}">{!! $t(...$m['label']) !!}</a>
                @endif
            @endforeach
            <a href="{{ route('account.login') }}" class="i-btn i-btn--block mt-3">{!! $tg !!}<span>{!! $t('Войти в кабинет', 'Sign in', 'Intră în cont') !!}</span></a>
        </div>
    </div>
</nav>
<main id="top" class="pub-main">
    @yield('content')
</main>

{{-- ───────────── FOOTER ───────────── --}}
<footer style="background:var(--bg-soft);border-top:1px solid var(--line);padding:56px 0 28px;">
    <div class="i-container">
        <div class="grid md:grid-cols-4 gap-10 mb-10">
            <div class="md:col-span-2">
                <img src="{{ \App\Support\PublicUi::versioned('brand/logo.png') }}" alt="INSPIRE EDU Network" width="130" height="66" style="height:66px;width:auto;margin-bottom:14px;">
                {!! $p('Платформа для партнёрства и развития будущих лидеров Молдовы. Реализуется при поддержке Посольства Королевства Нидерландов в Молдове.', 'Platform for partnership and development of future leaders of Moldova. Implemented with the support of the Embassy of the Kingdom of the Netherlands in Moldova.', 'Platformă pentru parteneriat și dezvoltarea viitorilor lideri ai Moldovei. Implementat cu sprijinul Ambasadei Regatului Țărilor de Jos în Moldova.', 'text-sm text-gray-500 leading-relaxed max-w-md') !!}
            </div>
            <div>
                <p class="i-eyebrow" style="color:var(--muted);margin-bottom:14px;">{!! $t('Разделы', 'Sections', 'Secțiuni') !!}</p>
                <div class="flex flex-col gap-2.5 text-sm font-semibold">
                    @foreach($menu as $m)
                        @if(isset($m['children']))
                            @foreach($m['children'] as $ch)
                                <a href="{{ route($ch['route']) }}" style="text-decoration:none">{!! $t(...$ch['label']) !!}</a>
                            @endforeach
                        @else
                            <a href="{{ route($m['route']) }}" style="text-decoration:none">{!! $t(...$m['label']) !!}</a>
                        @endif
                    @endforeach
                </div>
            </div>
            <div>
                <p class="i-eyebrow" style="color:var(--muted);margin-bottom:14px;">{!! $t('Контакты', 'Contacts', 'Contacte') !!}</p>
                <div class="flex flex-col gap-3 text-sm font-semibold">
                    <a href="{{ $bot }}" target="_blank" rel="noopener" class="flex items-center gap-2" style="text-decoration:none">{!! $tg !!} @Inspiremoldova_bot</a>
                    <a href="mailto:globalstudy.xxi@gmail.com" class="flex items-center gap-2" style="text-decoration:none">{!! $icon('bulb', 'w-[18px] h-[18px]') !!} globalstudy.xxi@gmail.com</a>
                    <a href="https://peace.education.md/" target="_blank" rel="noopener" class="flex items-center gap-2" style="text-decoration:none">{!! $icon('globe', 'w-[18px] h-[18px]') !!} peace.education.md</a>
                </div>
            </div>
        </div>
        <div class="i-spectrum-line" style="opacity:.8;margin-bottom:22px;"></div>
        <div class="flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-gray-500">
            <span>&copy; {{ date('Y') }} INSPIRE EDU Network &middot; Moldova</span>
            <span>{!! $t('Поддержано Посольством Нидерландов', 'Supported by the Embassy of the Netherlands', 'Susținut de Ambasada Țărilor de Jos') !!}</span>
        </div>
    </div>
</footer>

<script src="{{ \App\Support\PublicUi::versioned('design/inspire.js') }}"></script>
<script>
    const LANG_KEY = 'inspair_lang';
    function setLang(lang) {
        localStorage.setItem(LANG_KEY, lang);
        document.documentElement.lang = lang;
        document.querySelectorAll('.lang-btn').forEach(btn => btn.classList.toggle('is-active', btn.id === 'btn-' + lang));
    }
    setLang(document.documentElement.lang);

    // Desktop dropdown (Events): opens on hover via CSS, on click/Enter for touch and keyboard
    document.querySelectorAll('.nav-dd').forEach(function (dd) {
        var btn = dd.querySelector('.nav-dd__btn');
        btn.addEventListener('click', function (e) { e.stopPropagation(); var o = dd.classList.toggle('is-open'); btn.setAttribute('aria-expanded', o ? 'true' : 'false'); });
        document.addEventListener('click', function () { dd.classList.remove('is-open'); btn.setAttribute('aria-expanded', 'false'); });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') { dd.classList.remove('is-open'); btn.setAttribute('aria-expanded', 'false'); } });
    });

    // Mobile menu
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const mobileMenu    = document.getElementById('mobile-menu');
    const hamburgerIcon = document.getElementById('hamburger-icon');
    const closeIcon     = document.getElementById('close-icon');
    function closeMenu() {
        mobileMenu.classList.add('hidden'); hamburgerIcon.classList.remove('hidden'); closeIcon.classList.add('hidden');
        mobileMenuBtn.setAttribute('aria-expanded', 'false');
    }
    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', () => {
            if (!mobileMenu.classList.contains('hidden')) { closeMenu(); return; }
            mobileMenu.classList.remove('hidden'); hamburgerIcon.classList.add('hidden'); closeIcon.classList.remove('hidden');
            mobileMenuBtn.setAttribute('aria-expanded', 'true');
        });
        document.querySelectorAll('.mobile-nav-link').forEach(link => link.addEventListener('click', closeMenu));
    }
</script>
<script>
    // Telegram In-App Browser fix: links to the bot must be opened through Telegram.WebApp
    window.addEventListener('load', function () {
        if (!window.Telegram || !window.Telegram.WebApp) return;
        var tgApp = window.Telegram.WebApp;
        document.querySelectorAll('a[href*="t.me/Inspiremoldova_bot"]').forEach(function (link) {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                tgApp.openTelegramLink(link.href);
                tgApp.close();
            });
        });
    });
</script>
<script>
    // YouTube facade: the player is created only after a click
    document.addEventListener('click', function (e) {
        var b = e.target.closest('[data-yt]'); if (!b) return;
        var f = document.createElement('iframe');
        f.src = 'https://www.youtube-nocookie.com/embed/' + b.dataset.yt + '?autoplay=1&rel=0';
        f.allow = 'autoplay; encrypted-media; picture-in-picture'; f.allowFullscreen = true; f.title = b.dataset.title || 'Video';
        b.replaceWith(f);
    });
</script>
@stack('scripts')
</body>
</html>
