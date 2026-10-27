@extends('public.layout')

@php
    $T = $c['t'];
    $i = $n - 1;
    $sc = $site['events']['schools2025']['items'][$i];
    $titleEn = $sc['title']['en'];
@endphp

@section('title', $titleEn.' — Academy 2025')
@section('description', $sc['paras'][0]['en'])

@section('content')

<section class="page-head i-airy" style="padding-bottom:56px;">
    <div class="i-container">
        <a class="crumb" href="{{ route('academy2025') }}#schools">← {!! $t('Школы 2025', 'Schools 2025', 'Școli 2025') !!}</a>
        <p class="i-eyebrow i-in i-in-1" style="display:block">{!! $t('Школа ' . $n, 'School ' . $n, 'Școala ' . $n) !!}</p>
        <h1 class="i-h1 i-in i-in-2" style="margin:10px 0 0;max-width:900px;font-size:clamp(1.9rem,1.1rem + 2.8vw,3rem);">{!! $tm($sc['title']) !!}</h1>
        <div class="meta i-in i-in-3">
            <span>{!! $icon('cal', 'w-5 h-5') !!}{!! $tm($sc['dates']) !!}</span>
            <span>{!! $icon('pin', 'w-5 h-5') !!}{!! $tm($sc['place']) !!}</span>
        </div>
    </div>
</section>

<section class="i-section" style="padding-top:40px;">
    <div class="i-container">
        <p class="note">{!! $t('Подробная программа опубликована на английском языке.', 'The detailed programme is published in English.', 'Programul detaliat este publicat în limba engleză.') !!}</p>
        <div class="grid lg:grid-cols-[2fr_1fr] gap-6 items-start">
            <div class="i-card prose i-reveal">
                @foreach($sc['paras'] as $par){!! $pm($par, 'mb-4 text-gray-600') !!}@endforeach
                {!! $c['programmes'][$i] !!}
            </div>
            <aside class="i-card i-card--spectrum i-reveal lg:sticky" style="top:96px;">
                <p class="i-eyebrow">{!! $t('Ключевые темы', 'Key Focus Areas', 'Domenii-cheie') !!}</p>
                <ul class="text-sm text-gray-600 mb-5 mt-3" style="padding-left:1.1rem;list-style:disc;display:grid;gap:4px;">
                    @foreach($sc['areas'] as $ar)
                        <li>{!! $tm($ar) !!}</li>
                    @endforeach
                </ul>
                <a class="i-btn i-btn--secondary i-btn--block" href="{{ route('photo') }}#{{ ['school-1-chisinau', 'school-2-comrat', 'school-3-tiraspol'][$i] }}">{!! $t('Фото школы', 'Photos of the school', 'Fotografii de la școală') !!}</a>
            </aside>
        </div>
        <div class="flex flex-wrap gap-3 mt-10">
            @foreach([1, 2, 3] as $k)
                @if($k !== $n)
                    <a class="i-btn i-btn--secondary i-btn--sm" href="{{ route('academy2025.school', $k) }}">{!! $tk($T, 'school') !!} {{ $k }} →</a>
                @endif
            @endforeach
        </div>
    </div>
</section>
@endsection
