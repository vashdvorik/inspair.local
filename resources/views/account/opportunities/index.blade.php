@extends('account.layout')
@section('title', $tr('Возможности', 'Opportunities', 'Oportunități'))

@section('content')
<div class="cab-page cab-page--wide">

    <div class="mb-8">
        <h1 class="text-2xl font-bold tracking-tight text-[#0e2442]">{{ $tr('Возможности', 'Opportunities', 'Oportunități') }}</h1>
        <p class="mt-1.5 text-sm text-gray-500">{{ $tr('Учебные курсы и мероприятия за рубежом для студентов, лидеров и активистов', 'Training courses and events abroad for students, leaders and activists', 'Cursuri de formare și evenimente în străinătate pentru studenți, lideri și activiști') }}</p>
    </div>

    <div class="cab-opps">
        @foreach($items as $o)
            @php $href = route('account.opportunities.show', $o['slug']); @endphp
            <a href="{{ $href }}" class="cab-opp">
                <span class="cab-opp__photo"><img src="{{ $o['image_url'] }}" alt="" width="320" height="220" loading="lazy"></span>
                <span class="cab-opp__body">
                    <span class="cab-opp__meta">
                        <b>{{ $o['country'][$lang] ?? $o['country']['en'] }}</b>
                        @if($o['dates'])<span>{{ $o['dates'][$lang] ?? $o['dates']['en'] }}</span>@endif
                    </span>
                    <span class="cab-opp__title">{{ $o['name'] }}</span>
                    <span class="cab-opp__text">{{ $o['text'][$lang] ?? $o['text']['en'] }}</span>
                </span>
            </a>
        @endforeach
    </div>

</div>
@endsection
