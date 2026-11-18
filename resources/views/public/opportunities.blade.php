@extends('public.layout')

@section('title', 'Opportunities — INSPIRE EDU Network')
@section('description', 'News about international trips: training courses and events abroad for youth workers, leaders and activists.')

@section('content')
<section class="i-section" style="padding-top:124px">
    <div class="i-container">
        <div class="opp-rows">
            @foreach($items as $i => $o)
                @php $href = route('opportunities.show', $o['slug']); @endphp
                <article class="opp-row i-reveal" style="--d:{{ min($i, 4) * .05 }}s">
                    <a class="opp-row__photo" href="{{ $href }}" tabindex="-1" aria-hidden="true">
                        <img src="{{ $o['image_url'] }}" alt="" width="640" height="440" loading="lazy">
                    </a>
                    <div class="opp-row__body">
                        <p class="opp-row__meta">
                            <span class="opp-row__country">{!! $tm($o['country']) !!}</span>
                            @if($o['dates'])<span class="opp-row__dates">{!! $tm($o['dates']) !!}</span>@endif
                        </p>
                        <h2 class="opp-row__title"><a href="{{ $href }}">{!! $tm($o['title']) !!}</a></h2>
                        <p class="opp-row__text">{!! $tm($o['text']) !!}</p>
                        <a class="opp-row__more" href="{{ $href }}">{!! $t('Читать', 'Read', 'Citește') !!} <span aria-hidden="true">→</span></a>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
@endsection
