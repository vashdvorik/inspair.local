@extends('public.layout')

@section('title', 'Photo — INSPIRE EDU Network')
@section('description', 'Photo gallery of the INSPIRE schools 2023 and 2025, the Final Conference and meetings of the network.')

@php
    $S = \App\Support\PublicUi::content('site');
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
        <div class="i-filter" id="album-filter" role="group" aria-label="Albums">
            <button type="button" class="is-active" data-a="all">{!! $t('Все', 'All', 'Toate') !!}</button>
            @foreach($S['albums'] as $al)
                <button type="button" data-a="{{ $al['key'] }}">{!! $tm($al['title']) !!} <small style="opacity:.7">{{ count($gallery[$al['key']]) }}</small></button>
            @endforeach
        </div>
    </div>
</div>

<section class="i-section" style="padding-top:40px">
    <div class="i-container">
        @foreach($S['albums'] as $al)
            <div class="album mb-14" id="{{ $al['key'] }}" data-album="{{ $al['key'] }}">
                <div class="mb-6">
                    <h2 class="i-h3">{!! $tm($al['title']) !!}</h2>
                    <p class="text-sm text-gray-500 mt-1">{!! $tm($al['sub']) !!} · {{ count($gallery[$al['key']]) }} {!! $t('фото', 'photos', 'foto') !!}</p>
                </div>
                <div class="masonry" data-gallery>
                    @foreach($gallery[$al['key']] as $k => $ph)
                        <a href="{{ $gal($al['key'], $ph['f']) }}" aria-label="{{ $al['title']['en'] }} {{ $k + 1 }}"><img src="{{ $gal($al['key'], $ph['f'], true) }}" alt="" loading="lazy" decoding="async" width="{{ $ph['w'] }}" height="{{ $ph['h'] }}"></a>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</section>
@endsection

@push('scripts')
<script src="{{ asset('design/gallery.js') }}" defer></script>
<script>
    (function () {
        var tabs = document.querySelectorAll('#album-filter button'), albums = document.querySelectorAll('.album');
        function pick(key, push) {
            tabs.forEach(function (b) { b.classList.toggle('is-active', b.dataset.a === key); });
            albums.forEach(function (a) { a.style.display = (key === 'all' || a.dataset.album === key) ? '' : 'none'; });
            if (push) history.replaceState(null, '', key === 'all' ? location.pathname : '#' + key);
            window.scrollTo({ top: Math.max(0, document.querySelector('.album-tabs').offsetTop - 70), behavior: 'smooth' });
        }
        tabs.forEach(function (b) { b.addEventListener('click', function () { pick(b.dataset.a, true); }); });
        var h = location.hash.slice(1);
        if (h && document.getElementById(h) && document.getElementById(h).classList.contains('album')) pick(h, false);
    })();
</script>
@endpush
