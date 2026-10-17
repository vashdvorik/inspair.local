@extends('public.layout')

@section('title', 'Эксперты — INSPIRE EDU Network')
@section('description', 'Эксперты и наставники INSPIRE EDU Network: послы, представители ООН, ректоры, исследователи и практики, которые преподают на Академиях 2023 и 2025.')

@php
    $avatarTone = ['linear-gradient(135deg,#006de0,#019eff)', 'linear-gradient(135deg,#72299c,#006de0)', 'linear-gradient(135deg,#ff6c0b,#fdba07)', 'linear-gradient(135deg,#3d932e,#019eff)', 'linear-gradient(135deg,#e31e27,#ff6c0b)'];
    $n23 = count(array_filter($people, fn ($x) => in_array(2023, $x['years'])));
    $n25 = count(array_filter($people, fn ($x) => in_array(2025, $x['years'])));
@endphp

@section('content')

<section class="page-head i-airy">
    <div class="i-nodes" aria-hidden="true">
        <svg viewBox="0 0 1200 420" preserveAspectRatio="xMidYMid slice">
            <line x1="70" y1="330" x2="210" y2="210"/><line x1="210" y1="210" x2="360" y2="290"/><line x1="860" y1="80" x2="1000" y2="170"/><line x1="1000" y1="170" x2="1120" y2="90"/>
            <circle cx="70" cy="330" r="9" fill="#006de0"/><circle cx="210" cy="210" r="11" fill="#fdba07"/><circle cx="360" cy="290" r="8" fill="#72299c"/>
            <circle cx="860" cy="80" r="8" fill="#ff6c0b"/><circle cx="1000" cy="170" r="10" fill="#019eff"/><circle cx="1120" cy="90" r="7" fill="#3d932e"/>
        </svg>
    </div>
    <div class="i-container">
        <p class="i-eyebrow i-in i-in-1">INSPIRE EDU Network</p>
        <h1 class="i-h1 i-in i-in-2" style="margin:10px 0 16px;">{!! $t('Эксперты', 'Experts', 'Experți') !!}</h1>
        {!! $p('Послы, представители ООН, ректоры, исследователи и практики, которые преподают и наставляют молодых лидеров Молдовы.', 'Ambassadors, UN representatives, rectors, researchers and practitioners who teach and mentor Moldova’s young leaders.', 'Ambasadori, reprezentanți ONU, rectori, cercetători și practicieni care predau și îndrumă tinerii lideri din Moldova.', 'i-lead i-in i-in-3') !!}
        <div class="flex flex-wrap gap-8 mt-8 i-in i-in-4">
            <div><div class="i-h2 i-grad-text stat-num">{{ count($people) }}</div><p class="i-eyebrow" style="color:var(--muted);margin-top:4px">{!! $t('экспертов и наставников', 'experts and mentors', 'experți și mentori') !!}</p></div>
            <div><div class="i-h2 i-grad-text stat-num">{{ $n25 }}</div><p class="i-eyebrow" style="color:var(--muted);margin-top:4px">{!! $t('в Академии 2025', 'in Academy 2025', 'în Academia 2025') !!}</p></div>
            <div><div class="i-h2 i-grad-text stat-num">{{ $n23 }}</div><p class="i-eyebrow" style="color:var(--muted);margin-top:4px">{!! $t('в Академии 2023', 'in Academy 2023', 'în Academia 2023') !!}</p></div>
        </div>
    </div>
</section>

<section class="i-section" id="experts" style="padding-top:56px;">
    <div class="i-container">
        <div class="flex flex-wrap items-end justify-between gap-5 mb-10">
            <div class="i-reveal">
                <h2 class="i-h2">{!! $t('Эксперты и наставники', 'Experts and mentors', 'Experți și mentori') !!}</h2>
                <div class="i-spectrum-line" style="width:84px;margin-top:14px;"></div>
            </div>
            <div class="i-filter i-reveal" role="group" aria-label="Filter" id="exp-filter">
                <button type="button" class="is-active" data-f="all">{!! $t('Все', 'All', 'Toți') !!}</button>
                <button type="button" data-f="2025">{!! $t('Академия 2025', 'Academy 2025', 'Academia 2025') !!}</button>
                <button type="button" data-f="2023">{!! $t('Академия 2023', 'Academy 2023', 'Academia 2023') !!}</button>
            </div>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-5" id="exp-grid">
            @foreach($people as $i => $e)
                <article class="i-card i-card--hover person i-reveal" data-years="{{ implode(',', $e['years']) }}" style="--d:{{ ($i % 5) * .05 }}s">
                    <div class="ph" @if(!$e['img']) style="background:{{ $avatarTone[$i % count($avatarTone)] }}" @endif>
                        @if($e['img'])
                            <img src="{{ $img($e['img']) }}" alt="{{ $e['name']['en'] }}" loading="lazy" width="112" height="112">
                        @else
                            {{ $initials($e['name']['en']) }}
                        @endif
                    </div>
                    <h3>{!! $tm($e['name']) !!}</h3>
                    <p>{!! $tm($e['role']) !!}</p>
                    @if(!empty($e['roleByYear'][2023]))<p style="font-size:.78rem;opacity:.8;margin-top:6px"><b>2023:</b> {!! $tm($e['roleByYear'][2023]) !!}</p>@endif
                    <div class="yrs">
                        @if(in_array(2023, $e['years'])) <span class="i-badge">2023</span> @endif
                        @if(in_array(2025, $e['years'])) <span class="i-badge i-badge--yellow">2025</span> @endif
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>

<section class="i-section i-section--soft">
    <div class="i-container">
        <div class="i-airy i-reveal" style="border-radius:var(--r-2xl);border:1px solid var(--line);padding:60px 28px;text-align:center;box-shadow:var(--sh-md);">
            <h2 class="i-h2" style="max-width:640px;margin:0 auto 14px;">{!! $t('Хотите выступить экспертом?', 'Want to join as an expert?', 'Vrei să te alături ca expert?') !!}</h2>
            {!! $p('Напишите организаторам — мы будем рады новым наставникам и спикерам.', 'Write to the organisers — we are always glad to welcome new mentors and speakers.', 'Scrie organizatorilor — ne bucurăm mereu de noi mentori și vorbitori.', 'i-lead mx-auto mb-8') !!}
            <a href="mailto:globalstudy.xxi@gmail.com" class="i-btn i-btn--lg"><span>globalstudy.xxi@gmail.com</span>{!! $arrow !!}</a>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    (function () {
        var bar = document.getElementById('exp-filter'); if (!bar) return;
        bar.addEventListener('click', function (e) {
            var b = e.target.closest('button'); if (!b) return;
            bar.querySelectorAll('button').forEach(function (x) { x.classList.toggle('is-active', x === b); });
            var f = b.dataset.f;
            document.querySelectorAll('#exp-grid .person').forEach(function (c) {
                c.hidden = !(f === 'all' || c.dataset.years.split(',').indexOf(f) >= 0);
            });
        });
    })();
</script>
@endpush
