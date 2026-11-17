@extends('public.layout')

@section('title', 'Publications — INSPIRE EDU Network')
@section('description', 'Publications of the INSPIRE network: collections of articles, reports and materials you can read page by page.')

@push('head')
    <link rel="stylesheet" href="{{ \App\Support\PublicUi::versioned('design/flipbook.css') }}">
@endpush

@section('content')
<section class="i-section" style="padding-top:124px">
    <div class="i-container">
        <h1 class="i-sr-only">{!! $tm($site['publications']['title']) !!}</h1>
        @if(count($items))
            {{-- Издания: клик по обложке открывает читалку с листанием --}}
            <div class="pub-grid{{ count($items) === 1 ? ' pub-grid--one' : '' }}">
                @foreach($items as $i => $it)
                    <article class="pub-card i-reveal" style="--d:{{ $i * .06 }}s">
                        <a class="pub-card__cover" href="{{ route('publications.show', $it['slug']) }}" tabindex="-1" aria-hidden="true">
                            <span class="pub-card__book"><img src="{{ $it['cover_url'] }}" alt="" width="600" height="{{ round(600 / $it['ratio']) }}" loading="lazy"></span>
                            @if(! empty($it['year']))<span class="pub-card__badge">{{ $it['year'] }}</span>@endif
                        </a>
                        <div class="pub-card__body">
                            <h2 class="pub-card__title"><a href="{{ route('publications.show', $it['slug']) }}">{!! $tm($it['title']) !!}</a></h2>
                            @if(array_filter($it['description'] ?? []))
                                <p class="pub-card__text">{!! $tm($it['description']) !!}</p>
                            @endif
                            <p class="pub-card__meta">{{ $it['pages'] }} {!! $t('стр.', 'pages', 'pagini') !!}</p>
                            <a class="i-btn" href="{{ route('publications.show', $it['slug']) }}">{!! $t('Читать онлайн', 'Read online', 'Citește online') !!}</a>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="i-card stub i-reveal">
                <div class="ico">{!! $icon('doc', 'w-10 h-10') !!}</div>
                {!! $pm($site['publications']['lead'], 'i-lead') !!}
                <p class="text-sm text-gray-500 mt-4"><a href="mailto:globalstudy.xxi@gmail.com">globalstudy.xxi@gmail.com</a></p>
            </div>
        @endif
    </div>
</section>
@endsection
