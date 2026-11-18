@extends('public.layout')

@php $H = $site['hackathon']; @endphp

@section('title', 'INSPIRE Hackathon 2026 — INSPIRE EDU Network')
@section('description', $H['description']['en'])

@section('content')

{{-- ───────────── HEAD ───────────── --}}
<section class="page-head i-airy" style="padding-bottom:84px">
    <div class="i-nodes" aria-hidden="true">
        <svg viewBox="0 0 1200 520" preserveAspectRatio="xMidYMid slice">
            <line x1="860" y1="90" x2="1000" y2="190"/><line x1="1000" y1="190" x2="1120" y2="100"/>
            <circle cx="860" cy="90" r="8" fill="#ff6c0b"/><circle cx="1000" cy="190" r="10" fill="#019eff"/><circle cx="1120" cy="100" r="7" fill="#3d932e"/>
        </svg>
    </div>
    <div class="i-container">
        <div class="flex flex-wrap items-center gap-3 i-in i-in-1">
            <span class="i-chip"><span class="i-dot"></span>{!! $tm($H['facts'][0]['v']) !!}</span>
            <span class="i-chip">{!! $icon('pin', 'w-4 h-4') !!} {!! $tm($H['venue']['addr']) !!}</span>
        </div>
        <h1 class="i-h1 i-in i-in-2" style="margin:18px 0 12px">INSPIRE <span class="i-grad-text">Hackathon 2026</span></h1>
        <p class="i-in i-in-3" style="font-size:var(--fs-xl);font-weight:600;color:var(--ink);max-width:34ch;text-wrap:balance">{!! $tm($H['subtitle']) !!}</p>
        <div class="flex flex-wrap gap-4 mt-8 i-in i-in-5">
            <a href="{{ $H['apply_url'] }}" target="_blank" rel="noopener" class="i-btn i-btn--lg"><span>{!! $tm($H['apply']) !!}</span>{!! $arrow !!}</a>
            <a href="{{ route('hackathon.regulations') }}" class="i-btn i-btn--secondary i-btn--lg"><span>{!! $tm($H['rules']) !!}</span></a>
        </div>
    </div>
</section>

<div class="i-section--tint" style="padding:13px 0;overflow:hidden;border-block:1px solid var(--blue-100);">
    <div class="i-container" style="font-weight:700;font-size:.78rem;letter-spacing:.14em;line-height:1.6;text-wrap:balance;text-transform:uppercase;color:var(--navy-800);text-align:center">{!! $tm($H['ticker']) !!}</div>
</div>

{{-- ───────────── ABOUT ───────────── --}}
<section class="i-section" id="about">
    <div class="i-container">
        <div class="hk-about">
            <div>
                <h2 class="i-h2 i-reveal">{!! $tm($H['tagline']) !!}</h2>
                <div class="i-spectrum-line i-reveal" style="width:84px;margin:14px 0 24px"></div>
                @foreach($H['about'] as $para)
                    <div class="i-reveal"><p>{!! $tm($para) !!}</p></div>
                @endforeach
                <p class="i-eyebrow mt-6 mb-3 i-reveal">{!! $tm($H['themes_title']) !!}</p>
                <div class="areas i-reveal">@foreach($H['themes'] as $th)<span class="i-chip">{!! $tm($th) !!}</span>@endforeach</div>
            </div>
            <aside class="i-card i-card--spectrum hk-glance i-reveal">
                <h3 class="i-h4">{!! $tm($H['glance_title']) !!}</h3>
                <dl>
                    @foreach($H['facts'] as $f)
                        <div>
                            <dt>{!! $tm($f['k']) !!}</dt>
                            <dd>{!! $tm($f['v']) !!}@isset($f['s'])<small>{!! $tm($f['s']) !!}</small>@endisset</dd>
                        </div>
                    @endforeach
                </dl>
            </aside>
        </div>
    </div>
</section>

{{-- ───────────── LEARN ───────────── --}}
<section class="i-section i-section--tint" id="learn">
    <div class="i-container">
        <h2 class="i-h2 i-reveal">{!! $tm($H['learn_title']) !!}</h2>
        <div class="i-spectrum-line i-reveal" style="width:84px;margin:14px 0 14px"></div>
        <p class="text-gray-500 mb-8 i-reveal" style="max-width:56ch">{!! $tm($H['learn_lead']) !!}</p>
        <div class="hk-learn">
            @foreach($H['learn'] as $i => $it)
                <div class="i-card hk-learn__item i-reveal" style="--d:{{ ($i % 3) * .06 }}s">
                    <span class="i-card__icon {{ ['', 'i-card__icon--orange', 'i-card__icon--green', 'i-card__icon--purple', 'i-card__icon--yellow', 'i-card__icon--red'][$i % 6] }}">{!! $icon($it['i'], 'w-6 h-6') !!}</span>
                    <p>{!! $tm($it['t']) !!}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ───────────── HOW IT WORKS ───────────── --}}
<section class="i-section" id="how">
    <div class="i-container">
        <h2 class="i-h2 i-reveal">{!! $tm($H['journey_title']) !!}</h2>
        <div class="i-spectrum-line i-reveal" style="width:84px;margin:14px 0 30px"></div>
        <ol class="hk-steps">
            @foreach($H['journey'] as $i => $st)
                <li class="i-reveal" style="--d:{{ ($i % 3) * .06 }}s">
                    <span class="hk-steps__when">{!! $tm($st['w']) !!}</span>
                    <h3>{!! $tm($st['t']) !!}</h3>
                    <p>{!! $tm($st['d']) !!}</p>
                </li>
            @endforeach
        </ol>
    </div>
</section>

{{-- ───────────── TRACKS ───────────── --}}
<section class="i-section i-section--soft" id="tracks">
    <div class="i-container">
        <h2 class="i-h2 i-reveal">{!! $tm($H['tracks_title']) !!}</h2>
        <div class="i-spectrum-line i-reveal" style="width:84px;margin:14px 0 14px"></div>
        <p class="text-gray-500 mb-8 i-reveal" style="max-width:56ch">{!! $tm($H['tracks_lead']) !!}</p>
        <div class="hk-tracks">
            @foreach($H['tracks'] as $i => $tr)
                <article class="i-card i-card--hover hk-track i-reveal" style="--d:{{ ($i % 3) * .06 }}s">
                    <span class="hk-track__ico">{!! $icon($tr['i'], 'w-5 h-5') !!}</span>
                    <h3>{!! $tm($tr['t']) !!}</h3>
                    <p>{!! $tm($tr['d']) !!}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>

{{-- ───────────── DELIVERABLES + JURY ───────────── --}}
<section class="i-section" id="jury">
    <div class="i-container">
        <div class="hk-two">
            <div class="i-card hk-list i-reveal">
                <h2 class="i-h3">{!! $tm($H['deliver_title']) !!}</h2>
                <ul>@foreach($H['deliver'] as $it)<li>{!! $tm($it) !!}</li>@endforeach</ul>
            </div>
            <div class="i-card hk-list i-reveal" style="--d:.08s">
                <h2 class="i-h3">{!! $tm($H['criteria_title']) !!}</h2>
                <ul>@foreach($H['criteria'] as $it)<li>{!! $tm($it) !!}</li>@endforeach</ul>
            </div>
        </div>
        <p class="hk-more i-reveal"><a href="{{ route('hackathon.regulations') }}">{!! $tm($H['rules']) !!} {!! $arrow !!}</a></p>
    </div>
</section>

{{-- ───────────── VENUE ───────────── --}}
<section class="i-section i-section--tint" id="venue">
    <div class="i-container">
        <div class="hk-venue i-reveal">
            <div class="hk-venue__logos">
                <img src="{{ $img('partners/green-tech-hub.webp') }}" alt="Green Tech Hub" loading="lazy" style="max-width:170px">
                <img src="{{ $img('partners/utm.webp') }}" alt="Technical University of Moldova" loading="lazy" style="max-width:90px">
            </div>
            <div>
                <p class="i-eyebrow mb-2">{!! $tm($H['venue_title']) !!}</p>
                <h2 class="i-h3" style="margin-bottom:8px">{!! $tm($H['venue']['name']) !!}</h2>
                <p class="hk-venue__addr">{!! $icon('pin', 'w-4 h-4') !!}<span>{!! $tm($H['venue']['addr']) !!}</span></p>
                <a href="{{ $H['venue']['map'] }}" target="_blank" rel="noopener" class="hk-venue__map">{!! $tm($H['venue']['map_label']) !!} {!! $arrow !!}</a>
            </div>
        </div>
    </div>
</section>

{{-- ───────────── CTA ───────────── --}}
<section class="i-section">
    <div class="i-container">
        <div class="hk-band i-reveal">
            <h2 class="i-h2">{!! $tm($H['cta_title']) !!}</h2>
            <p>{!! $tm($H['cta_text']) !!}</p>
            <div class="hk-band__btns">
                <a href="{{ $H['apply_url'] }}" target="_blank" rel="noopener" class="i-btn i-btn--lg"><span>{!! $tm($H['apply']) !!}</span>{!! $arrow !!}</a>
                <a href="{{ route('hackathon.regulations') }}" class="i-btn i-btn--secondary i-btn--lg"><span>{!! $tm($H['rules']) !!}</span></a>
            </div>
        </div>
    </div>
</section>
@endsection
