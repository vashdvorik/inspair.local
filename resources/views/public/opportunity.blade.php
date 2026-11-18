@extends('public.layout')

@php
    $labels = [
        'explore'  => ['ru' => 'Что изучают участники', 'en' => 'What participants explore', 'ro' => 'Ce explorează participanții'],
        'format'   => ['ru' => 'Формат', 'en' => 'Format', 'ro' => 'Format'],
        'combines' => ['ru' => 'Проект сочетает', 'en' => 'The project combines', 'ro' => 'Proiectul îmbină'],
        'benefits' => ['ru' => 'Что даст участие', 'en' => 'What you will gain', 'ro' => 'Ce îți oferă participarea'],
        'audience' => ['ru' => 'Для кого', 'en' => 'Who it is for', 'ro' => 'Pentru cine este'],
    ];
@endphp

@section('title', $o['name'].' — INSPIRE EDU Network')
@section('description', $o['text']['en'])

@section('content')
<section class="i-section" style="padding-top:124px">
    <div class="i-container">
        <h1 class="i-sr-only">{{ $o['title']['en'] }}</h1>
        <div class="opp-page">
            <div class="opp-page__main">
                @foreach(['ru', 'en', 'ro'] as $l)
                    @php $d = $o['detail'][$l]; @endphp
                    <div data-lang="{{ $l }}">
                        @if(! empty($d['lead']))<p class="i-lead opp-page__lead">{{ $d['lead'] }}</p>@endif

                        @if(! empty($d['explore']))
                            <h2 class="opp-h">{{ $labels['explore'][$l] }}</h2>
                            <ul class="opp-list">@foreach($d['explore'] as $x)<li>{{ $x }}</li>@endforeach</ul>
                        @endif

                        @if(! empty($d['format']))
                            <h2 class="opp-h">{{ $labels['format'][$l] }}</h2>
                            <p class="opp-p">{{ $labels['combines'][$l] }} {{ $d['format'] }}.</p>
                        @endif

                        @if(! empty($d['benefits']))
                            <h2 class="opp-h">{{ $labels['benefits'][$l] }}</h2>
                            <ul class="opp-list">@foreach($d['benefits'] as $x)<li>{{ $x }}</li>@endforeach</ul>
                        @endif

                        @if(! empty($d['audience']))

                        <h2 class=\"opp-h\">{{ $labels['audience'][$l] }}</h2>

                        <p class=\"opp-p\">{{ $d['audience'] }}</p>

                        @endif

                        @if(! empty($d['note']))<p class="opp-note">{{ $d['note'] }}</p>@endif
                    </div>
                @endforeach
            </div>

            <aside class="opp-page__side">
                <div class="i-card opp-facts">
                    <figure class="opp-photo">
                        <img src="{{ $o['image_url'] }}" alt="" width="640" height="440">
                    </figure>
                    <dl>
                        <dt>{!! $t('Страна', 'Country', 'Țara') !!}</dt><dd>{!! $tm($o['country']) !!}</dd>
                        @if($o['dates'])<dt>{!! $t('Даты', 'Dates', 'Date') !!}</dt><dd>{!! $tm($o['dates']) !!}</dd>@endif
                        @if($o['deadline_text'])<dt>{!! $t('Срок подачи заявок', 'Application deadline', 'Termen de înscriere') !!}</dt><dd>{!! $tm($o['deadline_text']) !!}</dd>@endif
                    </dl>
                </div>
            </aside>
        </div>

        <p style="margin:36px 0 0"><a href="{{ route('opportunities') }}" class="i-btn i-btn--secondary">← {!! $t('Все возможности', 'All opportunities', 'Toate oportunitățile') !!}</a></p>
    </div>
</section>
@endsection
