@extends('public.layout')

@section('title', 'Hackathon regulations — INSPIRE EDU Network')
@section('description', 'Regulations of the INSPIRE Hackathon 2026 (official text, in Russian).')

@section('content')
<section class="page-head i-airy" style="padding-bottom:40px">
    <div class="i-container">
        <a href="{{ route('hackathon') }}" class="rg-back i-in i-in-1"><span aria-hidden="true">←</span> {!! $t('Хакатон INSPIRE 2026', 'INSPIRE Hackathon 2026', 'Hackathon INSPIRE 2026') !!}</a>
        <h1 class="i-h1 i-in i-in-2" style="margin:14px 0 0">{!! $t('Положение о хакатоне', 'Hackathon regulations', 'Regulamentul hackathonului') !!}</h1>
        <p class="rg-note i-in i-in-3">
            <span data-lang="ru">Официальный текст документа.</span>
            <span data-lang="en">Official text of the document, published in Russian.</span>
            <span data-lang="ro">Textul oficial al documentului, publicat în limba rusă.</span>
        </p>
    </div>
</section>

<section class="i-section" style="padding-top:8px">
    <div class="i-container rg">
        <nav class="rg-toc" aria-label="{{ $t('Содержание', 'Contents', 'Cuprins') }}">
            <ol>
                @foreach($reg['sections'] as $s)
                    <li><a href="#s{{ $s['n'] }}"><b>{{ $s['n'] }}</b><span>{{ $s['title'] }}</span></a></li>
                @endforeach
            </ol>
        </nav>

        <article class="rg-doc" lang="ru">
            <header class="rg-doc__head">
                <p class="rg-doc__kicker">{{ $reg['title'] }}</p>
                <p class="rg-doc__sub">{{ $reg['subtitle'] }}</p>
                <p class="rg-doc__theme">{{ $reg['theme'] }}</p>
            </header>

            @foreach($reg['sections'] as $s)
                <section id="s{{ $s['n'] }}" class="rg-sec">
                    <h2><span>{{ $s['n'] }}</span>{{ $s['title'] }}</h2>
                    @foreach($s['items'] as $it)
                        <p><b>{{ $it['num'] }}</b> {!! preg_replace('~(https?://[^\s<]+)~u', '<a href="$1" target="_blank" rel="noopener">$1</a>', e($it['text'])) !!}</p>
                        @if(!empty($it['bullets']))
                            <ul>
                                @foreach($it['bullets'] as $b)
                                    <li>@if($b['term'])<b>{{ $b['term'] }}</b> — @endif{{ $b['text'] }}</li>
                                @endforeach
                            </ul>
                        @endif
                    @endforeach
                </section>
            @endforeach

            <div class="rg-apply">
                <a href="{{ $site['hackathon']['apply_url'] }}" target="_blank" rel="noopener" class="i-btn i-btn--lg"><span>{!! $t('Подать заявку', 'Apply now', 'Înscrie-te') !!}</span>{!! $arrow !!}</a>
            </div>
        </article>
    </div>
</section>
@endsection
