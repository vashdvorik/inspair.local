{{-- Видео-отзывы лентой «историй»: ряд кружков, по клику полноэкранный просмотр Shorts подряд (плеер создаётся только после клика) --}}
@php $RV = \App\Support\PublicUi::content('review-videos'); $rvItems = $RV['items']; @endphp
<div class="rs" data-rs>
    <button type="button" class="rs-arrow rs-arrow--prev" data-rs-scroll="-1" aria-label="{{ $t('Назад', 'Previous', 'Înapoi') }}">
        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M15 5l-7 7 7 7"/></svg>
    </button>
    <button type="button" class="rs-arrow rs-arrow--next" data-rs-scroll="1" aria-label="{{ $t('Дальше', 'Next', 'Înainte') }}">
        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M9 5l7 7-7 7"/></svg>
    </button>
    <ul class="rs-list" data-rs-list>
        @foreach($rvItems as $i => $v)
            <li>
                <button type="button" class="rs-story" data-rs-open="{{ $i }}" aria-label="{{ $v['name']['en'] }}">
                    <span class="rs-ring">
                        <img src="{{ $img('reviews/video/'.$v['id'].'.webp') }}" alt="" loading="lazy" width="240" height="240" decoding="async">
                        <span class="rs-badge" aria-hidden="true"><svg viewBox="0 0 24 24" width="10" height="10" fill="currentColor"><path d="M8 5v14l11-7z"/></svg></span>
                    </span>
                    <span class="rs-name">{!! $tm($v['name']) !!}</span>
                </button>
            </li>
        @endforeach
    </ul>
</div>

<div class="rs-view" data-rs-view hidden role="dialog" aria-modal="true" aria-label="{{ $RV['title']['en'] }}">
    <button type="button" class="rs-x" data-rs-close aria-label="{{ $t('Закрыть', 'Close', 'Închide') }}">
        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18"/></svg>
    </button>
    <button type="button" class="rs-nav rs-nav--prev" data-rs-prev aria-label="{{ $t('Назад', 'Previous', 'Înapoi') }}">
        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 5l-7 7 7 7"/></svg>
    </button>
    <button type="button" class="rs-nav rs-nav--next" data-rs-next aria-label="{{ $t('Дальше', 'Next', 'Înainte') }}">
        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 5l7 7-7 7"/></svg>
    </button>
    <div class="rs-stage">
        <div class="rs-bars" data-rs-bars>@foreach($rvItems as $v)<i><b></b></i>@endforeach</div>
        <div class="rs-frame" data-rs-frame></div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    var root = document.querySelector('[data-rs]'); if (!root) return;
    var view = document.querySelector('[data-rs-view]');
    document.body.appendChild(view); // вне анимированных блоков, иначе position: fixed считается от них, а не от окна
    var frame = view.querySelector('[data-rs-frame]');
    var bars = view.querySelectorAll('[data-rs-bars] i');
    var items = @json(collect($rvItems)->map(fn ($v) => ['id' => $v['id'], 'name' => $v['name']])->values());
    var stories = root.querySelectorAll('.rs-story');
    var lang = function () { var l = document.documentElement.lang; return items[0].name[l] ? l : 'ru'; };
    var cur = -1, iframe = null, seen = {};
    try { seen = JSON.parse(localStorage.getItem('rs_seen') || '{}'); } catch (e) {}
    stories.forEach(function (b, i) { if (seen[items[i].id]) b.classList.add('is-seen'); });

    function post(msg) { try { iframe.contentWindow.postMessage(JSON.stringify(msg), '*'); } catch (e) {} }

    function show(i) {
        if (i < 0) i = 0;
        if (i >= items.length) { close(); return; }
        cur = i;
        var it = items[i];
        bars.forEach(function (bar, k) { bar.firstElementChild.style.width = k < i ? '100%' : '0'; });
        iframe = document.createElement('iframe');
        iframe.src = 'https://www.youtube-nocookie.com/embed/' + it.id + '?autoplay=1&playsinline=1&rel=0&controls=0&modestbranding=1&enablejsapi=1&origin=' + encodeURIComponent(location.origin);
        iframe.allow = 'autoplay; encrypted-media; picture-in-picture; fullscreen';
        iframe.title = it.name[lang()];
        iframe.addEventListener('load', function () { post({ event: 'listening', id: 1, channel: 'widget' }); });
        frame.replaceChildren(iframe);
        seen[it.id] = 1; stories[i].classList.add('is-seen');
        try { localStorage.setItem('rs_seen', JSON.stringify(seen)); } catch (e) {}
    }

    function open(i) { view.hidden = false; document.documentElement.classList.add('rs-lock'); show(i); view.querySelector('[data-rs-close]').focus(); }
    function close() { view.hidden = true; document.documentElement.classList.remove('rs-lock'); frame.replaceChildren(); iframe = null; cur = -1; }

    // Ролик сообщает о ходе и конце: ведём полоску и сами переходим к следующему
    window.addEventListener('message', function (e) {
        if (!iframe || e.source !== iframe.contentWindow || typeof e.data !== 'string') return;
        var d; try { d = JSON.parse(e.data); } catch (x) { return; }
        var info = d.event === 'infoDelivery' ? d.info : (d.event === 'onStateChange' ? { playerState: d.info } : null);
        if (!info) return;
        if (info.duration && info.currentTime != null && bars[cur]) bars[cur].firstElementChild.style.width = Math.min(100, info.currentTime / info.duration * 100) + '%';
        if (info.playerState === 0) show(cur + 1);
    });

    root.addEventListener('click', function (e) {
        var b = e.target.closest('[data-rs-open]'); if (!b || root.dataset.dragged) return;
        open(+b.dataset.rsOpen);
    });
    view.addEventListener('click', function (e) {
        if (e.target.closest('[data-rs-close]') || e.target === view || e.target.classList.contains('rs-stage')) close();
        else if (e.target.closest('[data-rs-next]')) show(cur + 1);
        else if (e.target.closest('[data-rs-prev]')) show(cur - 1);
    });
    document.addEventListener('keydown', function (e) {
        if (view.hidden) return;
        if (e.key === 'Escape') close(); else if (e.key === 'ArrowRight') show(cur + 1); else if (e.key === 'ArrowLeft') show(cur - 1);
    });

    // Ряд листается пальцем, колесом с Shift, стрелками по краям, а мышью его можно ещё и перетаскивать
    var list = root.querySelector('[data-rs-list]'), down = null, sx = 0, sl = 0;
    function edges() {
        var max = list.scrollWidth - list.clientWidth;
        root.classList.toggle('has-prev', list.scrollLeft > 4);
        root.classList.toggle('has-next', list.scrollLeft < max - 4);
    }
    list.addEventListener('scroll', edges, { passive: true });
    window.addEventListener('resize', edges);
    edges();
    root.querySelectorAll('[data-rs-scroll]').forEach(function (b) {
        b.addEventListener('click', function () { list.scrollBy({ left: +b.dataset.rsScroll * Math.max(240, list.clientWidth * 0.75), behavior: 'smooth' }); });
    });
    list.addEventListener('pointerdown', function (e) { if (e.pointerType !== 'mouse') return; down = true; sx = e.clientX; sl = list.scrollLeft; root.dataset.dragged = ''; });
    window.addEventListener('pointermove', function (e) {
        if (!down) return;
        var dx = e.clientX - sx;
        if (Math.abs(dx) > 5) { root.dataset.dragged = '1'; list.classList.add('is-drag'); }
        list.scrollLeft = sl - dx;
    });
    window.addEventListener('pointerup', function () {
        if (!down) return; down = null; list.classList.remove('is-drag');
        setTimeout(function () { delete root.dataset.dragged; }, 0);
    });
})();
</script>
@endpush
