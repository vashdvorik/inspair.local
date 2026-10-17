{{-- Admin panel look: INSPIRE academy style (blue, rounded cards) with subtle motion --}}
<style>
    :root { --mo-ink: #14243b; --mo-blue: #3a7fc1; --mo-deep: #17406f; --mo-gold: #e9a23b; }

    @keyframes mo-flow { 0%,100% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } }
    @keyframes mo-d1 { 0%,100% { transform: translate(0,0) scale(1); } 50% { transform: translate(70px,40px) scale(1.18); } }
    @keyframes mo-d2 { 0%,100% { transform: translate(0,0) scale(1); } 50% { transform: translate(-80px,-30px) scale(1.22); } }
    @keyframes mo-in { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: none; } }

    h1, h2, h3, .fi-header-heading, .fi-section-header-heading { letter-spacing: -.01em; }

    /* ── Login: flowing gradient with drifting blobs ── */
    .fi-simple-layout {
        position: relative; overflow: hidden; isolation: isolate;
        background: linear-gradient(135deg, #0f2a4a, #17406f 55%, #3a7fc1);
    }
    .fi-simple-layout::before, .fi-simple-layout::after {
        content: ""; position: absolute; border-radius: 50%; z-index: -1; pointer-events: none; will-change: transform;
    }
    .fi-simple-layout::before { width: 440px; height: 440px; background: radial-gradient(circle, rgba(111,176,232,.75), rgba(111,176,232,0) 68%); top: -140px; left: -120px; animation: mo-d1 15s ease-in-out infinite; }
    .fi-simple-layout::after  { width: 380px; height: 380px; background: radial-gradient(circle, rgba(233,162,59,.4), rgba(233,162,59,0) 68%); bottom: -160px; right: 4%; animation: mo-d2 18s ease-in-out infinite; }
    .fi-simple-main { border-radius: 20px; box-shadow: 0 24px 60px rgba(8, 30, 60, .35); animation: mo-in .6s cubic-bezier(.22,1,.36,1) both; }

    /* ── Panel: soft backdrop, animated cards ── */
    .fi-body:not(.fi-simple-layout) {
        background-image: radial-gradient(60rem 30rem at 100% -10%, rgba(58,127,193,.10), transparent 60%),
                          radial-gradient(40rem 24rem at -10% 110%, rgba(233,162,59,.08), transparent 60%);
        background-attachment: fixed;
    }
    .fi-section, .fi-ta-ctn, .fi-wi-stats-overview-stat, .fi-wi-chart, .fi-fo-wizard, .fi-in-entry-wrp {
        border-radius: 16px; animation: mo-in .5s cubic-bezier(.22,1,.36,1) both;
        transition: box-shadow .25s ease, transform .25s ease;
    }
    .fi-wi-stats-overview-stat:hover, .fi-section:hover { box-shadow: 0 14px 34px rgba(31,79,134,.12); }
    .fi-wi-stats-overview-stat:hover { transform: translateY(-3px); }
    .fi-sidebar-item-btn, .fi-btn, .fi-icon-btn { transition: background-color .2s ease, transform .2s ease, box-shadow .2s ease; }
    .fi-btn:not(:disabled):hover { transform: translateY(-1px); }
    .fi-sidebar-item-btn:hover { transform: translateX(2px); }
    .fi-ta-row { transition: background-color .15s ease; }

    @media (prefers-reduced-motion: reduce) {
        .fi-simple-layout, .fi-simple-layout::before, .fi-simple-layout::after, .fi-simple-main,
        .fi-section, .fi-ta-ctn, .fi-wi-stats-overview-stat, .fi-wi-chart, .fi-fo-wizard, .fi-in-entry-wrp { animation: none !important; }
        .fi-section, .fi-ta-ctn, .fi-wi-stats-overview-stat, .fi-sidebar-item-btn, .fi-btn { transition: none !important; transform: none !important; }
    }
</style>
