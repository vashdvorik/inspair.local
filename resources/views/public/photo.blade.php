@extends('public.layout')

@section('title', 'Photo — INSPIRE EDU Network')
@section('description', 'Photo gallery of the INSPIRE schools 2023 and 2025, the Final Conference and meetings of the network.')

@php
    $S = \App\Support\PublicUi::content('site');
    $arrowBack = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"/></svg>';
    $total = array_sum(array_map('count', $gallery));
@endphp

@section('content')
<section class="page-head i-airy" style="padding-bottom:48px">
    <div class="i-container">
        <p class="i-eyebrow i-in i-in-1">INSPIRE EDU Network</p>
        <h1 class="i-h1 i-in i-in-2" style="margin:10px 0 14px">{!! $tm($S['photo_page']['title']) !!}</h1>
        {!! $pm($S['photo_page']['lead'], 'i-lead i-in i-in-3') !!}
        <p class="mt-3 text-sm text-gray-500 i-in i-in-4">{{ $total }} {!! $t('фотографий', 'photos', 'fotografii') !!}</p>
    </div>
</section>

<div class="album-tabs">
    <div class="i-container">
        <div id="album-filter" role="group" aria-label="Albums">
            <div class="album-tabs__top">
                <span class="i-eyebrow">{!! $t('Альбомы', 'Albums', 'Albume') !!}</span>
                <button type="button" class="album-tabs__all is-active" data-a="all">{!! $t('Все альбомы', 'All albums', 'Toate albumele') !!} · {{ $total }}</button>
            </div>
            <div class="album-tiles">
                @foreach($S['albums'] as $al)
                    @php
                        $its = $gallery[$al['key']];
                        $cv = $al['cover'] < 0 ? $its[count($its) + $al['cover']] : $its[$al['cover']];
                    @endphp
                    <button type="button" class="album-tile" data-a="{{ $al['key'] }}">
                        <img src="{{ $gal($al['key'], $cv['f'], true) }}" alt="" loading="lazy" decoding="async" width="{{ min($cv['w'], 520) }}" height="{{ round(min($cv['w'], 520) * $cv['h'] / $cv['w']) }}">
                        <span class="album-tile__txt"><b>{!! $tm($al['title']) !!}</b><small>{!! $tm($al['sub']) !!}</small></span>
                        <span class="album-tile__n">{{ count($its) }}</span>
                    </button>
                @endforeach
            </div>
        </div>
    </div>
</div>
<section class="i-section" id="albums-start" style="padding-top:40px">
    <div class="i-container">
        <div class="album-bar" data-bar hidden>
            <button type="button" class="album-back" data-back>{!! $arrowBack !!}<span>{!! $t('Все альбомы', 'All albums', 'Toate albumele') !!}</span></button>
            <span class="album-bar__name" data-bar-name></span>
        </div>
        @foreach($S['albums'] as $al)
            @php $cnt = count($gallery[$al['key']]); @endphp
            <div class="album mb-14" id="{{ $al['key'] }}" data-album="{{ $al['key'] }}" data-total="{{ $cnt }}">
                <div class="album__head mb-6">
                    <div>
                        <h2 class="i-h3">{!! $tm($al['title']) !!}</h2>
                        <p class="text-sm text-gray-500 mt-1">{!! $tm($al['sub']) !!}</p>
                    </div>
                    <span class="album__count"><b data-shown>0</b> / {{ $cnt }} {!! $t('фото', 'photos', 'foto') !!}</span>
                </div>
                <div class="pgrid" data-gallery>
                    @foreach($gallery[$al['key']] as $k => $ph)
                        <a href="{{ $gal($al['key'], $ph['f']) }}" aria-label="{{ $al['title']['en'] }} {{ $k + 1 }}" @class(['is-tall' => ($ph['h'] ?? 0) > ($ph['w'] ?? 1)]) @if($k >= 12) hidden @endif><img @if($k < 12) src="{{ $gal($al['key'], $ph['f'], true) }}" @else data-src="{{ $gal($al['key'], $ph['f'], true) }}" @endif alt="" loading="lazy" decoding="async" width="{{ $ph['w'] }}" height="{{ $ph['h'] }}"></a>
                    @endforeach
                </div>
                @if($cnt > 12)
                    <div class="album__more">
                        <button type="button" class="i-btn i-btn--secondary" data-more><span>{!! $t('Показать ещё', 'Show more', 'Arată mai multe') !!}</span><small data-left></small></button>
                        <button type="button" class="album__all" data-all>{!! $t('Показать все', 'Show all', 'Arată toate') !!} ({{ $cnt }})</button>
                    </div>
                @endif
            </div>
        @endforeach
        <div class="album-bar album-bar--end" data-bar hidden>
            <button type="button" class="album-back" data-back>{!! $arrowBack !!}<span>{!! $t('Все альбомы', 'All albums', 'Toate albumele') !!}</span></button>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="{{ \App\Support\PublicUi::versioned('design/gallery.js') }}" defer></script>
<script>
    (function () {
        var STEP = 12;
        function reveal(album, n) {
            var hidden = album.querySelectorAll('.pgrid a[hidden]');
            for (var i = 0; i < hidden.length && i < n; i++) {
                var im = hidden[i].querySelector('img');
                if (im && im.dataset.src) { im.src = im.dataset.src; im.removeAttribute('data-src'); }
                hidden[i].hidden = false;
            }
            refresh(album);
        }
        function refresh(album) {
            var total = +album.dataset.total, left = album.querySelectorAll('.pgrid a[hidden]').length;
            album.querySelector('[data-shown]').textContent = total - left;
            var more = album.querySelector('.album__more');
            if (more) {
                more.style.display = left ? '' : 'none';
                more.querySelector('[data-left]').textContent = '+' + Math.min(STEP, left);
            }
        }
        document.querySelectorAll('.album').forEach(function (album) {
            refresh(album);
            var m = album.querySelector('[data-more]'), all = album.querySelector('[data-all]');
            if (m) m.addEventListener('click', function () { reveal(album, STEP); });
            if (all) all.addEventListener('click', function () { reveal(album, 9999); });
        });
        window.__photoReveal = reveal;
    })();
    (function () {
        var tabs = document.querySelectorAll('#album-filter [data-a]'), albums = document.querySelectorAll('.album');
        var bars = document.querySelectorAll('[data-bar]'), barName = document.querySelector('[data-bar-name]');
        function pick(key, push, noScroll) {
            tabs.forEach(function (b) { b.classList.toggle('is-active', b.dataset.a === key); });
            albums.forEach(function (a) {
                var on = key === 'all' || a.dataset.album === key;
                a.style.display = on ? '' : 'none';
                if (on && key !== 'all') { var shown = a.querySelectorAll('.pgrid a:not([hidden])').length; if (shown < 24) window.__photoReveal(a, 24 - shown); }
                if (on && key !== 'all' && barName) barName.innerHTML = a.querySelector('.album__head h2').innerHTML;
            });
            bars.forEach(function (b) { b.hidden = key === 'all'; });
            if (push) history.pushState({ a: key }, '', key === 'all' ? location.pathname : '#' + key);
            if (!noScroll) window.scrollTo({ top: Math.max(0, document.getElementById('albums-start').getBoundingClientRect().top + window.pageYOffset - 90), behavior: 'smooth' });
        }
        function fromHash() {
            var h = location.hash.slice(1), el = h && document.getElementById(h);
            return el && el.classList.contains('album') ? h : 'all';
        }
        tabs.forEach(function (b) { b.addEventListener('click', function () { pick(b.dataset.a, true); }); });
        document.querySelectorAll('[data-back]').forEach(function (b) { b.addEventListener('click', function () { pick('all', true); }); });
        window.addEventListener('popstate', function () { pick(fromHash(), false, true); });
        var start = fromHash();
        if (start !== 'all') pick(start, false);
    })();
</script>
@endpush
