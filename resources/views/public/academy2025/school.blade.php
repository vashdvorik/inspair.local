@extends('public.layout')

@php
    $T = $c['t'];
    $i = $n - 1;
    $titleEn = $T['en']['schools'][$i]['title'];
@endphp

@section('title', $titleEn.' — Academy 2025')
@section('description', $T['en']['schools'][$i]['text'])

@section('content')

<section class="page-head i-airy" style="padding-bottom:56px;">
    <div class="i-container">
        <a class="crumb" href="{{ route('academy2025') }}#schools">← {!! $t('Академия 2025', 'Academy 2025', 'Academia 2025') !!}</a>
        <p class="i-eyebrow i-in i-in-1" style="display:block">{!! $tk($T, 'school') !!} {{ $n }}</p>
        <h1 class="i-h1 i-in i-in-2" style="margin:10px 0 0;max-width:900px;font-size:clamp(1.9rem,1.1rem + 2.8vw,3rem);">{!! $tk($T, "schools.$i.title") !!}</h1>
        <div class="meta i-in i-in-3">
            <span>{!! $icon('cal', 'w-5 h-5') !!}{!! $tk($T, "schools.$i.dates") !!}</span>
            <span>{!! $icon('pin', 'w-5 h-5') !!}{!! $tk($T, "schools.$i.place") !!}</span>
        </div>
    </div>
</section>

<section class="i-section" style="padding-top:40px;">
    <div class="i-container">
        <p class="note">{!! $t('Подробная программа опубликована на английском языке.', 'The detailed programme is published in English.', 'Programul detaliat este publicat în limba engleză.') !!}</p>
        <div class="grid lg:grid-cols-[2fr_1fr] gap-6 items-start">
            <div class="i-card prose i-reveal">
                {!! $pk($T, "schools.$i.text", 'mb-4 text-gray-600') !!}
                {!! $c['programmes'][$i] !!}
            </div>
            <aside class="i-card i-card--spectrum i-reveal lg:sticky" style="top:96px;">
                <p class="i-eyebrow">{!! $tk($T, 'deadline') !!}</p>
                <h2 class="i-h3" style="margin:6px 0 14px;">{!! $tk($T, "schools.$i.dl") !!}</h2>
                <ul class="text-sm text-gray-500 mb-5" style="padding-left:1.1rem;list-style:disc;display:grid;gap:4px;">
                    @foreach($T['en']['schools'][$i]['areas'] as $j => $a)
                        <li>{!! $tk($T, "schools.$i.areas.$j") !!}</li>
                    @endforeach
                </ul>
                @if(!empty($c['forms'][$i]))
                    <a class="i-btn i-btn--block" href="{{ $c['forms'][$i] }}" target="_blank" rel="noopener">{!! $t('Регистрация', 'Register', 'Înregistrare') !!}</a>
                @endif
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
