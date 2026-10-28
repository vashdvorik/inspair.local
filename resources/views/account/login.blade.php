<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход — INSPIRE EDU Network</title>
    <meta name="robots" content="noindex,nofollow">
    <meta name="theme-color" content="#ffffff">
    <link rel="icon" href="{{ \App\Support\PublicUi::versioned('brand/favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ \App\Support\PublicUi::versioned('brand/favicon-32.png') }}">
    <link rel="apple-touch-icon" href="{{ \App\Support\PublicUi::versioned('brand/apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ \App\Support\PublicUi::versioned('site.webmanifest') }}">
    <link rel="stylesheet" href="{{ \App\Support\PublicUi::versioned('design/inspire.css') }}">
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
    <link rel="stylesheet" href="{{ \App\Support\PublicUi::versioned('design/tailwind.css') }}">
    <style>
        .login-art { position: fixed; inset: 0; z-index: -1; pointer-events: none; overflow: hidden; }
        .login-card { background: #fff; border: 1px solid var(--line); border-radius: var(--r-2xl); box-shadow: var(--sh-lg); padding: 34px 30px; }
        .step-dot { flex: none; width: 28px; height: 28px; border-radius: 50%; display: grid; place-items: center; font-size: .78rem; font-weight: 800; color: #fff; background: var(--grad-primary); box-shadow: var(--sh-primary); }
    </style>
</head>
<body class="i-body i-airy min-h-screen flex items-center justify-center px-4 py-10">

    {{-- decorative network (light) --}}
    <div class="i-nodes" aria-hidden="true">
        <svg viewBox="0 0 1200 700" preserveAspectRatio="xMidYMid slice">
            <line x1="80" y1="140" x2="230" y2="260"/><line x1="230" y1="260" x2="140" y2="440"/><line x1="1080" y1="520" x2="960" y2="400"/><line x1="960" y1="400" x2="1090" y2="250"/>
            <circle cx="80" cy="140" r="9" fill="#006de0"/><circle cx="230" cy="260" r="11" fill="#fdba07"/><circle cx="140" cy="440" r="8" fill="#72299c"/>
            <circle cx="1080" cy="520" r="9" fill="#3d932e"/><circle cx="960" cy="400" r="10" fill="#ff6c0b"/><circle cx="1090" cy="250" r="8" fill="#019eff"/>
        </svg>
    </div>

    {{-- TMA loading overlay (hidden by default, shown via JS when inside Telegram) --}}
    <div id="tma-loading" style="display:none" class="fixed inset-0 z-50 flex-col items-center justify-center bg-white">
        <img src="{{ \App\Support\PublicUi::versioned('brand/logo-mark.png') }}" alt="" class="i-float mb-4" style="width:96px;height:auto">
        <div class="w-9 h-9 rounded-full animate-spin mb-4" style="border:4px solid var(--blue-100);border-top-color:var(--blue-600)"></div>
        <p class="text-sm text-gray-500 font-medium">Выполняется вход…</p>
    </div>

    {{-- TMA: not a member message --}}
    <div id="tma-not-member" style="display:none" class="w-full max-w-sm text-center">
        <img src="{{ \App\Support\PublicUi::versioned('brand/logo.png') }}" alt="INSPIRE EDU Network" class="mx-auto mb-6" style="height:84px;width:auto">
        <div class="login-card">
            <p class="font-bold text-ink mb-1">Доступ пока закрыт</p>
            <p class="text-sm text-gray-500">Ваша заявка ещё не одобрена или не подана. Напишите боту /start чтобы подать заявку.</p>
        </div>
    </div>

    {{-- Normal login form (hidden when inside Telegram) --}}
    <div id="login-form-wrapper" class="w-full max-w-sm i-in">
        <div class="flex flex-col items-center mb-7 text-center">
            <img src="{{ \App\Support\PublicUi::versioned('brand/logo.png') }}" alt="INSPIRE EDU Network" width="190" height="97" style="height:96px;width:auto">
            <p class="i-eyebrow mt-3" style="color:var(--muted)">Личный кабинет участника</p>
        </div>

        @if(session('error'))
            <div class="i-alert i-alert--danger mb-4" role="alert">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ session('error') }}
            </div>
        @endif

        <div class="login-card text-center">
            <h1 class="i-h3 mb-5" style="font-size:1.35rem">Войти через Telegram</h1>

            <div class="text-left grid gap-3.5 mb-7">
                <div class="flex items-center gap-3">
                    <span class="step-dot">1</span>
                    <p class="text-sm text-gray-600 font-medium">Откройте бота по кнопке ниже</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="step-dot" style="background:linear-gradient(135deg,var(--orange),var(--yellow));box-shadow:0 8px 20px rgba(255,108,11,.25)">2</span>
                    <p class="text-sm text-gray-600 font-medium">Перейдите по ссылке из сообщения бота</p>
                </div>
            </div>

            <a href="https://t.me/Inspiremoldova_bot?start=login" id="tg-login-btn" target="_blank" rel="noopener" class="i-btn i-btn--block">
                <svg class="w-[18px] h-[18px]" fill="currentColor" viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
                Открыть @Inspiremoldova_bot
            </a>
            <p class="mt-4 text-xs text-gray-500">Доступ только для одобренных участников</p>
        </div>

        <p class="text-center mt-6 text-sm">
            <a href="/" class="font-semibold">← Вернуться на сайт</a>
        </p>
    </div>{{-- /login-form-wrapper --}}

    <script src="{{ \App\Support\PublicUi::versioned('design/inspire.js') }}"></script>
    <script>
        // ── Telegram Mini App: auto-auth ──────────────────────────────────────
        (function () {
            var tg = window.Telegram && window.Telegram.WebApp;
            if (!tg || !tg.initData) return;

            // If the user explicitly logged out (?logout=1), do NOT auto-login —
            // show the form so they can choose to log back in manually.
            var params = new URLSearchParams(window.location.search);
            if (params.get('logout') === '1') {
                document.getElementById('login-form-wrapper').style.display = 'block';
                return;
            }

            // We're inside Telegram — hide form, show spinner, attempt auto-auth
            document.getElementById('login-form-wrapper').style.display = 'none';
            document.getElementById('tma-loading').style.display = 'flex';

            tg.ready();

            fetch('{{ route('account.tma-auth') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ init_data: tg.initData })
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.ok) {
                    window.location.href = data.redirect;
                } else if (data.reason === 'not_member') {
                    document.getElementById('tma-loading').style.display = 'none';
                    document.getElementById('tma-not-member').style.display = 'block';
                } else {
                    // Unexpected error — fall back to normal form
                    document.getElementById('tma-loading').style.display = 'none';
                    document.getElementById('login-form-wrapper').style.display = 'block';
                }
            })
            .catch(function () {
                document.getElementById('tma-loading').style.display = 'none';
                document.getElementById('login-form-wrapper').style.display = 'block';
            });
        })();

        // ── Telegram button behaviour in regular browser ──────────────────────
        (function () {
            var btn = document.getElementById('tg-login-btn');
            if (!btn) return;

            btn.addEventListener('click', function (e) {
                if (window.Telegram && window.Telegram.WebApp) {
                    e.preventDefault();
                    window.Telegram.WebApp.openTelegramLink(
                        'https://t.me/Inspiremoldova_bot?start=login'
                    );
                    window.Telegram.WebApp.close();
                }
                // Иначе — обычный браузер, открывается стандартная ссылка target="_blank"
            });
        })();
    </script>
</body>
</html>
