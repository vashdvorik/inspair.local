{{-- Admin panel skin — INSPIRE design system: light, airy, one theme. Tokens come from /design/inspire.css --}}
<link rel="stylesheet" href="{{ asset('design/inspire.css') }}">
<style>
    :root { color-scheme: light; }
    /* never dark, even if the OS asks for it */
    html, html.dark { color-scheme: light; }

    h1, h2, h3, .fi-header-heading, .fi-section-header-heading { letter-spacing: -.015em; }
    .fi-header-heading { font-weight: 800; color: var(--ink); }

    /* ── Login: bright airy page with soft light blobs ── */
    .fi-simple-layout {
        position: relative; overflow: hidden; isolation: isolate;
        background: linear-gradient(180deg, #ffffff 0%, var(--bg-soft) 55%, var(--bg-tint) 100%);
    }
    .fi-simple-layout::before, .fi-simple-layout::after { content: ""; position: absolute; border-radius: 50%; z-index: -1; pointer-events: none; will-change: transform; }
    .fi-simple-layout::before { width: 620px; height: 620px; top: -240px; right: -120px; background: radial-gradient(circle, rgba(1,158,255,.20), rgba(1,158,255,0) 68%); animation: i-drift-a 24s ease-in-out infinite; }
    .fi-simple-layout::after  { width: 540px; height: 540px; bottom: -240px; left: -140px; background: radial-gradient(circle, rgba(253,186,7,.20), rgba(253,186,7,0) 68%); animation: i-drift-b 28s ease-in-out infinite; }
    .fi-simple-main { border-radius: var(--r-2xl) !important; border: 1px solid var(--line); background: #fff; box-shadow: var(--sh-lg) !important; animation: i-rise .7s var(--ease) both; }
    .fi-simple-main .fi-logo { margin-inline: auto; }

    /* ── Panel: calm light backdrop ── */
    .fi-body:not(.fi-simple-layout) {
        background-color: var(--bg-soft);
        background-image: radial-gradient(60rem 30rem at 100% -10%, rgba(1,158,255,.09), transparent 60%),
                          radial-gradient(40rem 24rem at -10% 110%, rgba(253,186,7,.08), transparent 60%);
        background-attachment: fixed;
    }
    .fi-topbar { background: rgba(255,255,255,.96) !important; border-bottom: 1px solid var(--line); box-shadow: none !important; }
    .fi-sidebar { background: #fff !important; border-right: 1px solid var(--line); box-shadow: var(--sh-sm); }
    .fi-sidebar-header { border-bottom: 0 !important; box-shadow: none !important; background: #fff !important; }
    .fi-sidebar::before { content: ""; display: block; height: 3px; background: var(--spectrum); }
    .fi-sidebar-item-btn { border-radius: 14px !important; transition: background-color .2s ease, transform .2s var(--ease); }
    .fi-sidebar-item-btn:hover { transform: translateX(2px); }
    .fi-sidebar-item.fi-active .fi-sidebar-item-btn { background: var(--blue-100) !important; }
    .fi-sidebar-item.fi-active .fi-sidebar-item-label, .fi-sidebar-item.fi-active .fi-icon { color: var(--navy-800) !important; font-weight: 700; }

    /* cards, tables, widgets: rounded, blue-tinted shadows, gentle entrance */
    .fi-section, .fi-ta-ctn, .fi-wi-stats-overview-stat, .fi-wi-chart, .fi-fo-wizard, .fi-in-entry-wrp {
        border-radius: var(--r-lg) !important; border: 1px solid var(--line) !important; box-shadow: var(--sh-sm) !important; background: #fff;
        animation: i-rise .55s var(--ease) both; transition: box-shadow .25s ease, transform .25s var(--ease), border-color .25s;
    }
    .fi-wi-stats-overview-stat:hover { transform: translateY(-3px); box-shadow: var(--sh-md) !important; border-color: var(--blue-200) !important; }
    .fi-ta-header-cell { background: var(--bg-soft) !important; }
    .fi-ta-row:hover { background: var(--blue-50) !important; }

    /* buttons */
    .fi-btn { border-radius: var(--r-pill) !important; transition: background-color .2s ease, transform .2s var(--ease), box-shadow .2s ease; }
    .fi-btn:not(:disabled):hover { transform: translateY(-1px); }
    .fi-color-primary.fi-btn.fi-btn-color-primary:not(.fi-outlined) { background: var(--grad-primary) !important; box-shadow: var(--sh-primary); border: 0; }
    .fi-input-wrp { border-radius: var(--r-md) !important; }
    .fi-badge { font-weight: 700; }

    @media (prefers-reduced-motion: reduce) {
        .fi-simple-layout::before, .fi-simple-layout::after, .fi-simple-main,
        .fi-section, .fi-ta-ctn, .fi-wi-stats-overview-stat, .fi-wi-chart, .fi-fo-wizard, .fi-in-entry-wrp { animation: none !important; }
        .fi-section, .fi-ta-ctn, .fi-wi-stats-overview-stat, .fi-sidebar-item-btn, .fi-btn { transition: none !important; transform: none !important; }
    }
</style>
