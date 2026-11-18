{{-- Карточка возможности: фото, страна и даты, название, краткое описание и срок заявок (одна на список и на главную) --}}
<a href="{{ route('account.opportunities.show', $o['slug']) }}" class="cab-opp">
    <span class="cab-opp__photo"><img src="{{ $o['image_url'] }}" alt="" width="320" height="220" loading="lazy"></span>
    <span class="cab-opp__body">
        <span class="cab-opp__meta">
            <b>{{ $o['country'][$lang] ?? $o['country']['en'] }}</b>
            @if($o['dates'])<span>{{ $o['dates'][$lang] ?? $o['dates']['en'] }}</span>@endif
        </span>
        <span class="cab-opp__title">{{ $o['title'][$lang] ?? $o['name'] }}</span>
        <span class="cab-opp__text">{{ $o['text'][$lang] ?? $o['text']['en'] }}</span>
        @if(! empty($o['deadline']) && $o['deadline_text'] && \Illuminate\Support\Carbon::parse($o['deadline'])->endOfDay()->isFuture())
            <span class="cab-opp__deadline">{{ $tr('Заявки до', 'Apply by', 'Înscrieri până la') }} {{ $o['deadline_text'][$lang] ?? $o['deadline_text']['en'] }}</span>
        @endif
    </span>
</a>
