@extends('public.layout')

@section('title', 'Партнёры — INSPIRE EDU Network')
@section('description', 'Партнёры INSPIRE EDU Network: донор, университеты-организаторы и организации, поддерживающие Академии 2023 и 2025.')

@php
    $groups = [
        'donor'   => ['Донор', 'Donor', 'Donator'],
        'coorg'   => ['Организаторы', 'Organisers', 'Organizatori'],
        'partner' => ['Партнёры', 'Partners', 'Parteneri'],
    ];
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
        <h1 class="i-h1 i-in i-in-2" style="margin:10px 0 16px;">{!! $t('Партнёры', 'Partners', 'Parteneri') !!}</h1>
        {!! $p('Университеты, международные организации и НПО, которые вместе с нами строят сообщество молодых лидеров Молдовы.', 'Universities, international organisations and NGOs that build the community of Moldova’s young leaders together with us.', 'Universități, organizații internaționale și ONG-uri care construiesc împreună cu noi comunitatea tinerilor lideri din Moldova.', 'i-lead i-in i-in-3') !!}
        <div class="flex flex-wrap gap-8 mt-8 i-in i-in-4">
            <div><div class="i-h2 i-grad-text stat-num">{{ count($partners) }}</div><p class="i-eyebrow" style="color:var(--muted);margin-top:4px">{!! $t('организаций', 'organisations', 'organizații') !!}</p></div>
            <div><div class="i-h2 i-grad-text stat-num">{{ count(array_filter($partners, fn ($x) => $x['group'] === 'coorg')) }}</div><p class="i-eyebrow" style="color:var(--muted);margin-top:4px">{!! $t('университетов-организаторов', 'organising institutions', 'instituții organizatoare') !!}</p></div>
        </div>
    </div>
</section>

<section class="i-section" id="partners" style="padding-top:56px;">
    <div class="i-container">
        <div class="flex flex-wrap items-end justify-between gap-5 mb-10">
            <div class="i-reveal">
                <h2 class="i-h2">{!! $t('Организации и партнёры', 'Organisations and partners', 'Organizații și parteneri') !!}</h2>
                <div class="i-spectrum-line" style="width:84px;margin-top:14px;"></div>
            </div>
            <div class="i-filter i-reveal" role="group" aria-label="Filter" id="par-filter">
                <button type="button" class="is-active" data-f="all">{!! $t('Все', 'All', 'Toți') !!}</button>
                <button type="button" data-f="2025">{!! $t('Академия 2025', 'Academy 2025', 'Academia 2025') !!}</button>
                <button type="button" data-f="2023">{!! $t('Академия 2023', 'Academy 2023', 'Academia 2023') !!}</button>
            </div>
        </div>
        @foreach($groups as $key => $title)
            @php $list = array_values(array_filter($partners, fn ($x) => $x['group'] === $key)); @endphp
            @if($list)
                <div class="par-group" style="margin-top:{{ $loop->first ? 0 : 38 }}px">
                    <p class="group-title">{!! $t(...$title) !!}</p>
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                        @foreach($list as $j => $pt)
                            @if($pt['url'])
                                <a class="logo-tile par-item i-reveal" data-years="{{ implode(',', $pt['years']) }}" style="--d:{{ ($j % 5) * .05 }}s" href="{{ $pt['url'] }}" target="_blank" rel="noopener" title="{{ $pt['name'] }}"><img src="{{ $img($pt['img']) }}" alt="{{ $pt['name'] }}" loading="lazy"></a>
                            @else
                                <span class="logo-tile par-item i-reveal" data-years="{{ implode(',', $pt['years']) }}" style="--d:{{ ($j % 5) * .05 }}s" title="{{ $pt['name'] }}"><img src="{{ $img($pt['img']) }}" alt="{{ $pt['name'] }}" loading="lazy"></span>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach
    </div>
</section>

<section class="i-section i-section--soft">
    <div class="i-container">
        <div class="i-airy i-reveal" style="border-radius:var(--r-2xl);border:1px solid var(--line);padding:60px 28px;text-align:center;box-shadow:var(--sh-md);">
            <h2 class="i-h2" style="max-width:660px;margin:0 auto 14px;">{!! $t('Станьте партнёром INSPIRE', 'Become an INSPIRE partner', 'Devino partener INSPIRE') !!}</h2>
            {!! $p('Хотите поддержать академии и сообщество молодых лидеров? Свяжитесь с организаторами.', 'Would you like to support the academies and the community of young leaders? Get in touch with the organisers.', 'Vrei să sprijini academiile și comunitatea tinerilor lideri? Contactează organizatorii.', 'i-lead mx-auto mb-8') !!}
            <a href="mailto:globalstudy.xxi@gmail.com" class="i-btn i-btn--lg"><span>globalstudy.xxi@gmail.com</span>{!! $arrow !!}</a>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    (function () {
        var bar = document.getElementById('par-filter'); if (!bar) return;
        bar.addEventListener('click', function (e) {
            var b = e.target.closest('button'); if (!b) return;
            bar.querySelectorAll('button').forEach(function (x) { x.classList.toggle('is-active', x === b); });
            var f = b.dataset.f;
            document.querySelectorAll('.par-item').forEach(function (c) { c.style.display = (f === 'all' || c.dataset.years.split(',').indexOf(f) >= 0) ? '' : 'none'; });
            document.querySelectorAll('.par-group').forEach(function (g) { g.style.display = g.querySelector('.par-item:not([style*="display: none"])') ? '' : 'none'; });
        });
    })();
</script>
@endpush
