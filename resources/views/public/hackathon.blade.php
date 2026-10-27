@extends('public.layout')

@section('title', 'Hackathon 2026 — INSPIRE EDU Network')
@section('description', 'INSPIRE Hackathon 2026: information coming soon.')

@section('content')
<section class="page-head i-airy" style="padding-bottom:84px">
    <div class="i-container">
        <span class="i-badge i-badge--orange i-in i-in-1">{!! $t('Скоро', 'Coming soon', 'În curând') !!}</span>
        <h1 class="i-h1 i-in i-in-2" style="margin:16px 0 0">{!! $tm($site['hackathon']['title']) !!}</h1>
    </div>
</section>
<section class="i-section" style="padding-top:0">
    <div class="i-container">
        <div class="i-card stub i-reveal">
            <div class="ico">{!! $icon('code', 'w-10 h-10') !!}</div>
            {!! $pm($site['hackathon']['lead'], 'i-lead') !!}
            {!! $pm($site['hackathon']['note'], 'text-sm text-gray-500 mt-4') !!}
            <a href="{{ $bot }}" target="_blank" rel="noopener" class="i-btn i-btn--lg mt-7">{!! $tg !!}<span>{!! $t('Присоединиться', 'Join now', 'Alătură-te') !!}</span></a>
        </div>
    </div>
</section>
@endsection
