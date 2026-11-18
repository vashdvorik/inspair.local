@extends('account.layout')
@section('title', $o['name'])

@php
    $d = $o['detail'][$lang] ?? $o['detail']['en'];
@endphp

@section('content')
<div class="cab-page cab-page--wide">

    <a href="{{ route('account.opportunities.index') }}" class="cab-oppd__back">← {{ $tr('Все возможности', 'All opportunities', 'Toate oportunitățile') }}</a>

    <article class="cab-oppd">
        <div class="cab-oppd__main">
            <figure class="cab-oppd__photo"><img src="{{ $o['image_url'] }}" alt="" width="640" height="440"></figure>

            <div class="cab-oppd__meta">
                <b>{{ $o['country'][$lang] ?? $o['country']['en'] }}</b>
                @if($o['dates'])<span>{{ $o['dates'][$lang] ?? $o['dates']['en'] }}</span>@endif
            </div>
            <h1 class="cab-oppd__title">{{ $o['name'] }}</h1>

            <p class="cab-oppd__lead">{{ $d['lead'] }}</p>

            @if(! empty($d['explore']))
                <h2>{{ $tr('Что изучают участники', 'What participants explore', 'Ce explorează participanții') }}</h2>
                <ul>@foreach($d['explore'] as $x)<li>{{ $x }}</li>@endforeach</ul>
            @endif

            @if(! empty($d['format']))
                <h2>{{ $tr('Формат', 'Format', 'Format') }}</h2>
                <p>{{ $tr('Проект сочетает', 'The project combines', 'Proiectul îmbină') }} {{ $d['format'] }}.</p>
            @endif

            @if(! empty($d['benefits']))
                <h2>{{ $tr('Что даст участие', 'What you will gain', 'Ce îți oferă participarea') }}</h2>
                <ul>@foreach($d['benefits'] as $x)<li>{{ $x }}</li>@endforeach</ul>
            @endif

            <h2>{{ $tr('Для кого', 'Who it is for', 'Pentru cine este') }}</h2>
            <p>{{ $d['audience'] }}</p>

            @if(! empty($d['note']))<p class="cab-oppd__note">{{ $d['note'] }}</p>@endif
        </div>

        <aside class="cab-oppd__facts">
            <dl>
                <dt>{{ $tr('Страна', 'Country', 'Țara') }}</dt><dd>{{ $o['country'][$lang] ?? $o['country']['en'] }}</dd>
                @if($o['dates'])<dt>{{ $tr('Даты', 'Dates', 'Date') }}</dt><dd>{{ $o['dates'][$lang] ?? $o['dates']['en'] }}</dd>@endif
                @if($o['deadline_text'])<dt>{{ $tr('Срок подачи заявок', 'Application deadline', 'Termen de înscriere') }}</dt><dd>{{ $o['deadline_text'][$lang] ?? $o['deadline_text']['en'] }}</dd>@endif
            </dl>
        </aside>
    </article>

</div>
@endsection
