@extends('public.layout')

@section('title', $item['title']['en'].' — INSPIRE EDU Network')
@section('description', $item['description']['en'] ?: $item['title']['en'])

@push('head')
    <link rel="stylesheet" href="{{ \App\Support\PublicUi::versioned('design/flipbook.css') }}">
@endpush

{{-- Читалка с листанием. В разметке только адреса страниц-картинок (WebP, их сделал tools/pdf-to-pages.py из PDF):
     design/flipbook.js подгружает текущую страницу и несколько соседних, а не все сразу. Без скриптов остаётся первая страница. --}}
@section('content')
<section class="page-head i-airy" style="padding-bottom:36px">
    <div class="i-container">
        <p class="i-eyebrow i-in i-in-1">{!! $t('Публикации', 'Publications', 'Publicații') !!}</p>
        <h1 class="i-h1 i-in i-in-2" style="margin:10px 0 0">{!! $tm($item['title']) !!}</h1>
        @if(array_filter($item['description'] ?? []))
            <p class="i-lead i-in i-in-3" style="margin:14px 0 0;max-width:720px">{!! $tm($item['description']) !!}</p>
        @endif
    </div>
</section>

<section class="i-section" style="padding-top:8px">
    <div class="i-container">
        <div class="pub-flip" data-flip style="--r: {{ $item['ratio'] }}" data-ratio="{{ $item['ratio'] }}" data-pages="{{ json_encode($item['page_urls']) }}">
            <div class="pub-flip__stage">
                <div class="pub-flip__viewport" data-flip-viewport>
                    <div class="pub-flip__fit"><div class="pub-flip__book" data-flip-book></div></div>
                </div>
                <noscript><a href="{{ $item['page_urls'][0] }}"><img src="{{ $item['page_urls'][0] }}" alt="" class="pub-flip__first"></a></noscript>
            </div>

            <div class="pub-flip__bar" data-flip-bar hidden>
                <button type="button" class="pub-flip__btn" data-flip-prev>
                    <span aria-hidden="true">←</span>
                    <span class="i-sr-only">{!! $t('Назад', 'Previous', 'Înapoi') !!}</span>
                </button>
                <span class="pub-flip__count" data-flip-count aria-live="polite"></span>
                <button type="button" class="pub-flip__btn" data-flip-next>
                    <span aria-hidden="true">→</span>
                    <span class="i-sr-only">{!! $t('Вперёд', 'Next', 'Înainte') !!}</span>
                </button>
                <button type="button" class="pub-flip__btn pub-flip__btn--text pub-flip__btn--zoom" data-flip-zoom-in>
                    <span class="pub-flip__icon" aria-hidden="true">+</span>
                    <span class="pub-flip__label">{!! $t('Приблизить', 'Zoom in', 'Mărește') !!}</span>
                </button>
                <button type="button" class="pub-flip__btn pub-flip__btn--text pub-flip__btn--zoom" data-flip-zoom-out disabled>
                    <span class="pub-flip__icon" aria-hidden="true">−</span>
                    <span class="pub-flip__label">{!! $t('Уменьшить', 'Zoom out', 'Micșorează') !!}</span>
                </button>
                <button type="button" class="pub-flip__btn pub-flip__btn--text" data-flip-full aria-pressed="false">
                    <span data-flip-full-label="enter">{!! $t('На весь экран', 'Full screen', 'Ecran complet') !!}</span>
                    <span data-flip-full-label="exit" hidden>{!! $t('Свернуть', 'Exit full screen', 'Restrânge') !!}</span>
                </button>
            </div>
            <p class="pub-flip__hint" data-flip-hint hidden>{!! $t(
                'Листайте страницы пальцем влево или вправо. Чтобы прокрутить сайт, проведите вверх или вниз по полям рядом с листом.',
                'Swipe left or right to turn pages. To scroll the site, swipe up or down on the margins beside the page.',
                'Trageți spre stânga sau spre dreapta pentru a întoarce pagina. Pentru a derula site-ul, trageți în sus sau în jos pe marginile de lângă pagină.'
            ) !!}</p>
        </div>

        <p style="margin:28px 0 0"><a href="{{ route('publications') }}" class="i-btn i-btn--secondary">← {!! $t('Все публикации', 'All publications', 'Toate publicațiile') !!}</a></p>
    </div>
</section>
@endsection

@push('scripts')
    <script src="{{ \App\Support\PublicUi::versioned('design/page-flip.browser.js') }}"></script>
    <script src="{{ \App\Support\PublicUi::versioned('design/flipbook.js') }}"></script>
@endpush
