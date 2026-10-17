@extends('public.layout')

@section('title', 'Академия 2023 — Youth Leaders Peace Summer School')
@section('description', 'Летняя школа «Молодёжные лидеры за мир», 14–18 сентября 2023: программа, эксперты и партнёры.')

@php
    $experts23 = array_values(array_filter($people, fn ($x) => in_array(2023, $x['years'])));
    $partners23 = array_values(array_filter($partners, fn ($x) => in_array(2023, $x['years'])));
    $avatarTone = ['linear-gradient(135deg,#006de0,#019eff)', 'linear-gradient(135deg,#72299c,#006de0)', 'linear-gradient(135deg,#ff6c0b,#fdba07)', 'linear-gradient(135deg,#3d932e,#019eff)'];
@endphp

@section('content')

{{-- ───────────── HEAD ───────────── --}}
<section class="page-head i-airy" style="padding-bottom:84px;">
    <div class="i-nodes" aria-hidden="true">
        <svg viewBox="0 0 1200 520" preserveAspectRatio="xMidYMid slice">
            <line x1="70" y1="400" x2="210" y2="270"/><line x1="210" y1="270" x2="360" y2="350"/><line x1="860" y1="90" x2="1000" y2="190"/><line x1="1000" y1="190" x2="1120" y2="100"/>
            <circle cx="70" cy="400" r="9" fill="#006de0"/><circle cx="210" cy="270" r="11" fill="#fdba07"/><circle cx="360" cy="350" r="8" fill="#72299c"/>
            <circle cx="860" cy="90" r="8" fill="#ff6c0b"/><circle cx="1000" cy="190" r="10" fill="#019eff"/><circle cx="1120" cy="100" r="7" fill="#3d932e"/>
        </svg>
    </div>
    <div class="i-container">
        <div class="flex flex-wrap items-center gap-3 i-in i-in-1">
            <span class="i-chip"><span class="i-dot"></span>{!! $tm($c['dates']) !!}</span>
            <span class="i-badge i-badge--yellow">{!! $t('Архив', 'Archive', 'Arhivă') !!}</span>
        </div>
        <h1 class="i-h1 i-in i-in-2" style="margin:18px 0 12px;">YOUTH LEADERS<br><span class="i-grad-text">PEACE SUMMER SCHOOL</span></h1>
        <p class="i-in i-in-3" style="font-size:var(--fs-xl);font-weight:600;color:var(--ink);">{!! $tm($c['subtitle']) !!}</p>
        {!! $pm($c['archiveNote'], 'i-lead i-in i-in-4 mt-3') !!}
        <div class="flex flex-wrap gap-4 mt-8 i-in i-in-5">
            <a href="#programme" class="i-btn i-btn--lg">{!! $tm($c['programmeH']) !!}</a>
            <a href="#experts" class="i-btn i-btn--secondary i-btn--lg"><span>{!! $t('Эксперты', 'Experts', 'Experți') !!}</span>{!! $arrow !!}</a>
        </div>
    </div>
</section>

<div class="i-section--tint" style="padding:13px 0;overflow:hidden;white-space:nowrap;border-block:1px solid var(--blue-100);">
    <div class="i-container" style="font-weight:700;font-size:.78rem;letter-spacing:.14em;text-transform:uppercase;color:var(--navy-800);text-align:center">{!! $tm($c['ticker']) !!}</div>
</div>

{{-- ───────────── ABOUT ───────────── --}}
<section class="i-section" id="about">
    <div class="i-container">
        <h2 class="i-h2 i-reveal">{!! $tm($c['aboutH']) !!}</h2>
        <div class="i-spectrum-line i-reveal" style="width:84px;margin:14px 0 26px;"></div>
        <div class="grid md:grid-cols-2 gap-8">
            @foreach($c['about'] as $i => $par)
                <div class="i-reveal" style="--d:{{ $i * .08 }}s">{!! $pm($par, 'text-gray-600 leading-relaxed') !!}</div>
            @endforeach
        </div>
        <div class="i-card i-card--spectrum i-reveal" style="margin-top:38px;padding:34px;">
            <div class="grid md:grid-cols-[auto_1fr] gap-6 items-start">
                <div class="i-card__icon i-card__icon--green" style="width:60px;height:60px;border-radius:20px;margin:0">{!! $icon('heart', 'w-7 h-7') !!}</div>
                <div>
                    <h3 class="i-h3" style="margin-bottom:10px">{!! $tm($c['valuesH']) !!}</h3>
                    {!! $pm($c['values'], 'text-gray-600 leading-relaxed') !!}
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ───────────── FOR YOU ───────────── --}}
<section class="i-section i-section--tint" id="for-you">
    <div class="i-container i-container--narrow">
        <h2 class="i-h2 i-reveal">{!! $tm($c['forYouH']) !!}</h2>
        <div class="i-spectrum-line i-reveal" style="width:84px;margin:14px 0 26px;"></div>
        <ul class="grid md:grid-cols-2 gap-4">
            @foreach($c['forYou'] as $i => $item)
                <li class="i-card i-reveal" style="--d:{{ $i * .05 }}s;padding:20px 22px;display:flex;gap:14px;align-items:flex-start;"><span class="i-dot" style="margin-top:8px;flex:none"></span><span>{!! $tm($item) !!}</span></li>
            @endforeach
        </ul>
        <div class="mt-8 i-reveal"><a class="i-btn i-btn--secondary" href="{{ $c['formUrl'] }}" target="_blank" rel="noopener"><span>{!! $t('Форма регистрации 2023 (архив)', '2023 registration form (archive)', 'Formularul de înscriere 2023 (arhivă)') !!}</span>{!! $arrow !!}</a></div>
    </div>
</section>

{{-- ───────────── PROGRAMME ───────────── --}}
<section class="i-section i-section--soft" id="programme">
    <div class="i-container i-container--narrow">
        <h2 class="i-h2 i-reveal">{!! $tm($c['programmeH']) !!}</h2>
        <div class="i-spectrum-line i-reveal" style="width:84px;margin:14px 0 14px;"></div>
        {!! $pm($c['programmeNote'], 'text-gray-500 mb-8 i-reveal') !!}
        <ol class="timeline">
            @foreach($c['programme'] as $i => $item)
                <li class="i-reveal" style="--d:{{ ($i % 6) * .04 }}s">{!! $tm($item) !!}</li>
            @endforeach
        </ol>
    </div>
</section>

{{-- ───────────── EXPERTS ───────────── --}}
<section class="i-section" id="experts">
    <div class="i-container">
        <div class="flex flex-wrap items-end justify-between gap-4 mb-9">
            <div class="i-reveal"><h2 class="i-h2">{!! $t('Наши эксперты', 'Our experts', 'Experții noștri') !!}</h2><div class="i-spectrum-line" style="width:84px;margin-top:14px;"></div></div>
            <a href="{{ route('experts') }}" class="i-btn i-btn--secondary i-reveal"><span>{!! $t('Все эксперты', 'All experts', 'Toți experții') !!}</span>{!! $arrow !!}</a>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-5">
            @foreach($experts23 as $i => $e)
                <article class="i-card i-card--hover person i-reveal" style="--d:{{ ($i % 5) * .05 }}s">
                    <div class="ph" @if(!$e['img']) style="background:{{ $avatarTone[$i % 4] }}" @endif>
                        @if($e['img']) <img src="{{ $img($e['img']) }}" alt="{{ $e['name']['en'] }}" loading="lazy" width="112" height="112"> @else {{ $initials($e['name']['en']) }} @endif
                    </div>
                    <h3>{!! $tm($e['name']) !!}</h3>
                    <p>{!! $tm($e['roleByYear'][2023] ?? $e['role']) !!}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>

{{-- ───────────── PHOTO ───────────── --}}
<section class="i-section" id="photo">
    <div class="i-container">
        <div class="flex flex-wrap items-end justify-between gap-4 mb-6">
            <div class="i-reveal"><h2 class="i-h2">{!! $t('Фото школы', 'Photos of the school', 'Fotografii de la școală') !!}</h2><div class="i-spectrum-line" style="width:84px;margin-top:14px;"></div></div>
            <span class="i-chip i-reveal">{{ count($photos['y2023']) }} {!! $t('фото', 'photos', 'fotografii') !!}</span>
        </div>
        {!! $p('Фотоархив восстановлен из сохранившихся миниатюр: оригиналы в большом размере не сохранились.', 'The photo archive was restored from the thumbnails that survived; the full-size originals are no longer available.', 'Arhiva foto a fost restaurată din miniaturile păstrate; originalele în dimensiune mare nu s-au păstrat.', 'text-sm text-gray-500 mb-8 i-reveal') !!}
        <div class="gallery" id="gallery">
            @foreach($photos['y2023'] as $k => $ph)
                <a class="gal-item" href="{{ $img($ph) }}" data-i="{{ $k }}" aria-label="{{ $k + 1 }}"><img src="{{ $img($ph) }}" alt="" loading="lazy" width="150" height="150"></a>
            @endforeach
        </div>
    </div>
</section>

{{-- ───────────── PARTNERS ───────────── --}}
<section class="i-section i-section--soft">
    <div class="i-container">
        <h2 class="i-h2 i-reveal" style="text-align:center">{!! $t('Донор и партнёры', 'Donor and partners', 'Donator și parteneri') !!}</h2>
        <div class="i-spectrum-line i-reveal" style="width:84px;margin:14px auto 30px;"></div>
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-4">
            @foreach($partners23 as $j => $pt)
                @if($pt['url'])
                    <a class="logo-tile i-reveal" style="--d:{{ ($j % 6) * .04 }}s" href="{{ $pt['url'] }}" target="_blank" rel="noopener" title="{{ $pt['name'] }}"><img src="{{ $img($pt['img']) }}" alt="{{ $pt['name'] }}" loading="lazy"></a>
                @else
                    <span class="logo-tile i-reveal" style="--d:{{ ($j % 6) * .04 }}s" title="{{ $pt['name'] }}"><img src="{{ $img($pt['img']) }}" alt="{{ $pt['name'] }}" loading="lazy"></span>
                @endif
            @endforeach
        </div>
        <p class="text-center mt-10 text-sm text-gray-500">{!! $t('Связаться с организаторами:', 'Contact the organisers:', 'Contactați organizatorii:') !!} <a class="font-semibold" href="mailto:{{ $c['contact'] }}">{{ $c['contact'] }}</a></p>
    </div>
</section>

{{-- ───────────── NEXT ───────────── --}}
<section class="i-section">
    <div class="i-container">
        <div class="i-airy i-reveal" style="border-radius:var(--r-2xl);border:1px solid var(--line);padding:56px 28px;text-align:center;box-shadow:var(--sh-md);">
            <h2 class="i-h2" style="max-width:640px;margin:0 auto 14px;">{!! $t('Следующий шаг — Академия 2025', 'The next step — Academy 2025', 'Următorul pas — Academia 2025') !!}</h2>
            {!! $p('Три школы в Кишинёве, Комрате и Тирасполе. Регистрация открыта.', 'Three schools in Chisinau, Comrat and Tiraspol. Registration is open.', 'Trei școli la Chișinău, Comrat și Tiraspol. Înregistrarea este deschisă.', 'i-lead mx-auto mb-8') !!}
            <a href="{{ route('academy2025') }}" class="i-btn i-btn--lg"><span>{!! $t('Академия 2025', 'Academy 2025', 'Academia 2025') !!}</span>{!! $arrow !!}</a>
        </div>
    </div>
</section>
@endsection
