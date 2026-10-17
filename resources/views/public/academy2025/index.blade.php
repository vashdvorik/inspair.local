@extends('public.layout')

@section('title', 'Академия 2025 — INSPIRE Leadership Academy')
@section('description', 'INSPIRE Leadership Academy 2025: три школы в Кишинёве, Комрате и Тирасполе для молодых лидеров, которые меняют регион и мир.')

@php
    $T = $c['t'];
    $schoolTone = ['', 'yellow', 'green'];
    $experts25 = array_values(array_filter($people, fn ($x) => in_array(2025, $x['years']) && $x['img']));
    $partners25 = array_values(array_filter($partners, fn ($x) => in_array(2025, $x['years']) && in_array($x['group'], ['donor', 'coorg', 'partner'])));
@endphp

@section('content')

{{-- ───────────── HEAD ───────────── --}}
<section class="page-head i-airy">
    <div class="i-nodes" aria-hidden="true">
        <svg viewBox="0 0 1200 520" preserveAspectRatio="xMidYMid slice">
            <line x1="70" y1="400" x2="210" y2="270"/><line x1="210" y1="270" x2="360" y2="350"/><line x1="860" y1="90" x2="1000" y2="190"/><line x1="1000" y1="190" x2="1120" y2="100"/>
            <circle cx="70" cy="400" r="9" fill="#006de0"/><circle cx="210" cy="270" r="11" fill="#fdba07"/><circle cx="360" cy="350" r="8" fill="#72299c"/>
            <circle cx="860" cy="90" r="8" fill="#ff6c0b"/><circle cx="1000" cy="190" r="10" fill="#019eff"/><circle cx="1120" cy="100" r="7" fill="#3d932e"/>
        </svg>
    </div>
    <div class="i-container grid lg:grid-cols-[1.3fr_.7fr] gap-10 items-center">
        <div>
            <p class="i-eyebrow i-in i-in-1">{!! $t('Академия 2025', 'Academy 2025', 'Academia 2025') !!}</p>
            <h1 class="i-h1 i-in i-in-2" style="margin:10px 0 16px;">INSPIRE <span class="i-grad-text">Leadership Academy</span> 2025</h1>
            {!! $p('Помогаем молодым лидерам менять регион и мир к лучшему.', 'Empowering young leaders to drive regional and global change.', 'Împuternicim tinerii lideri să schimbe în bine regiunea și lumea.', 'i-lead i-in i-in-3') !!}
            <div class="meta i-in i-in-4">
                <span>{!! $icon('cal', 'w-5 h-5') !!}{!! $t('Май — ноябрь 2025', 'May — November 2025', 'Mai — noiembrie 2025') !!}</span>
                <span>{!! $icon('pin', 'w-5 h-5') !!}{!! $t('Кишинёв · Комрат · Тирасполь', 'Chisinau · Comrat · Tiraspol', 'Chișinău · Comrat · Tiraspol') !!}</span>
            </div>
            <div class="flex flex-wrap gap-4 mt-8 i-in i-in-5">
                <a href="#deadlines" class="i-btn i-btn--lg">{!! $t('Регистрация', 'Register', 'Înregistrare') !!}</a>
                <a href="#schools" class="i-btn i-btn--secondary i-btn--lg"><span>{!! $t('Смотреть школы', 'Explore the schools', 'Descoperă școlile') !!}</span>{!! $arrow !!}</a>
            </div>
        </div>
        <div class="i-in i-in-3 hidden lg:block"><img class="i-float" src="{{ $img('academy-2025/leadership.png') }}" alt="INSPIRE Leadership Academy 2025" width="380" height="356" style="width:min(360px,100%);height:auto;margin:0 auto;"></div>
    </div>
</section>

{{-- ───────────── ABOUT ───────────── --}}
<section class="i-section" id="about" style="padding-top:56px;">
    <div class="i-container">
        <h2 class="i-h2 i-reveal">{!! $tk($T, 'aboutH') !!}</h2>
        <div class="i-spectrum-line i-reveal" style="width:84px;margin:14px 0 26px;"></div>
        <div class="grid md:grid-cols-2 gap-8 text-gray-600 leading-relaxed i-reveal">
            <div>{!! $tk($T, 'about.0') !!}</div>
            <div>{!! $tk($T, 'about.1') !!}</div>
        </div>
        <h3 class="i-h4 i-reveal" style="margin:30px 0 14px;">{!! $tk($T, 'valuesH') !!}</h3>
        <div class="flex flex-wrap gap-2.5 i-reveal">
            @foreach($T['en']['values'] as $i => $v)
                <span class="i-chip" style="font-size:.85rem;padding:.5rem 1rem">{!! $tk($T, 'values.'.$i) !!}</span>
            @endforeach
        </div>
    </div>
</section>

{{-- ───────────── SCHOOLS ───────────── --}}
<section class="i-section i-section--tint" id="schools">
    <div class="i-container">
        <h2 class="i-h2 i-reveal">{!! $tk($T, 'schoolsH') !!}</h2>
        <div class="i-spectrum-line i-reveal" style="width:84px;margin:14px 0 18px;"></div>
        {!! $pk($T, 'schoolsLead', 'i-lead i-reveal') !!}
        <div class="grid lg:grid-cols-3 gap-6 mt-10">
            @foreach($T['en']['schools'] as $i => $s)
                <article class="i-card i-card--hover i-reveal" style="--d:{{ $i * .1 }}s;display:flex;flex-direction:column;">
                    <span class="i-eyebrow">{!! $tk($T, 'school') !!} {{ $i + 1 }}</span>
                    <h3 class="i-h3" style="font-size:1.25rem;margin:8px 0 12px;">{!! $tk($T, "schools.$i.title") !!}</h3>
                    <div class="flex flex-wrap gap-x-5 gap-y-1 text-sm font-semibold text-ink mb-3">
                        <span class="inline-flex items-center gap-1.5">{!! $icon('cal', 'w-4 h-4 text-orange') !!}{!! $tk($T, "schools.$i.dates") !!}</span>
                        <span class="inline-flex items-center gap-1.5">{!! $icon('pin', 'w-4 h-4 text-orange') !!}{!! $tk($T, "schools.$i.place") !!}</span>
                    </div>
                    {!! $pk($T, "schools.$i.text", 'text-sm text-gray-500 leading-relaxed mb-3') !!}
                    <ul class="text-sm text-gray-500 mb-5" style="padding-left:1.1rem;list-style:disc;display:grid;gap:3px;">
                        @foreach($s['areas'] as $j => $a)
                            <li>{!! $tk($T, "schools.$i.areas.$j") !!}</li>
                        @endforeach
                    </ul>
                    <div style="margin-top:auto;padding-top:16px;border-top:1px solid var(--line);">
                        <p class="text-xs text-gray-500 mb-3">{!! $tk($T, 'deadline') !!} <strong class="text-ink">{!! $tk($T, "schools.$i.dl") !!}</strong></p>
                        <a href="{{ route('academy2025.school', $i + 1) }}" class="i-btn i-btn--secondary i-btn--sm"><span>{!! $t('Программа', 'Programme', 'Program') !!}</span>{!! $arrow !!}</a>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>

{{-- ───────────── AUDIENCE + FEATURES ───────────── --}}
<section class="i-section">
    <div class="i-container">
        <h2 class="i-h2 i-reveal">{!! $tk($T, 'audH') !!}</h2>
        <div class="i-spectrum-line i-reveal" style="width:84px;margin:14px 0 30px;"></div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
            @foreach($T['en']['audience'] as $i => $a)
                <div class="i-card i-card--hover i-reveal" style="--d:{{ $i * .07 }}s">
                    <h3 class="i-h4" style="margin-bottom:6px;">{!! $tk($T, "audience.$i.0") !!}</h3>
                    <p class="text-sm text-gray-500">{!! $tk($T, "audience.$i.1") !!}</p>
                </div>
            @endforeach
        </div>
        <div class="grid md:grid-cols-2 gap-5 mt-5">
            <div class="i-card i-card--tint i-reveal"><h3 class="i-h4" style="margin-bottom:6px;">{!! $tk($T, 'langH') !!}</h3><p class="text-sm text-gray-500">{!! $tk($T, 'langT') !!}</p></div>
            <div class="i-card i-card--tint i-reveal" style="--d:.08s"><h3 class="i-h4" style="margin-bottom:6px;">{!! $tk($T, 'selH') !!}</h3><p class="text-sm text-gray-500">{!! $tk($T, 'selT') !!}</p></div>
        </div>
        <p class="mt-6 font-bold text-ink i-reveal">{!! $tk($T, 'audClose') !!}</p>

        <h2 class="i-h2 i-reveal" style="margin-top:64px;">{!! $tk($T, 'featH') !!}</h2>
        <div class="i-spectrum-line i-reveal" style="width:84px;margin:14px 0 30px;"></div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @php $ftone = ['', 'yellow', 'green', 'purple', 'orange', 'red']; @endphp
            @foreach($T['en']['features'] as $i => $f)
                <div class="i-card i-card--hover i-reveal" style="--d:{{ ($i % 3) * .07 }}s">
                    <div class="i-card__icon {{ $ftone[$i % 6] ? 'i-card__icon--'.$ftone[$i % 6] : '' }}" style="width:44px;height:44px;margin-bottom:12px;">{!! $icon(['users','book','bolt','group','bulb','award','globe','heart','badge'][$i % 9], 'w-5 h-5') !!}</div>
                    <h3 class="i-h4" style="margin-bottom:6px;">{!! $tk($T, "features.$i.0") !!}</h3>
                    <p class="text-sm text-gray-500">{!! $tk($T, "features.$i.1") !!}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ───────────── DEADLINES ───────────── --}}
<section class="i-section i-section--soft" id="deadlines">
    <div class="i-container">
        <h2 class="i-h2 i-reveal">{!! $tk($T, 'regH') !!}</h2>
        <div class="i-spectrum-line i-reveal" style="width:84px;margin:14px 0 30px;"></div>
        <div class="grid md:grid-cols-3 gap-6">
            @foreach($T['en']['schools'] as $i => $s)
                <div class="i-card i-reveal" style="--d:{{ $i * .08 }}s;display:flex;flex-direction:column;">
                    <span class="i-eyebrow">{!! $tk($T, 'school') !!} {{ $i + 1 }}</span>
                    <h3 class="i-h4" style="margin:6px 0 8px;">{!! $tk($T, "schools.$i.title") !!}</h3>
                    <p class="text-sm text-gray-500 mb-5">{!! $tk($T, 'deadline') !!} <strong class="text-ink">{!! $tk($T, "schools.$i.dl") !!}</strong></p>
                    @if(!empty($c['forms'][$i]))
                        <a class="i-btn" style="margin-top:auto;align-self:flex-start" href="{{ $c['forms'][$i] }}" target="_blank" rel="noopener">{!! $t('Регистрация', 'Register', 'Înregistrare') !!}</a>
                    @endif
                </div>
            @endforeach
        </div>
        <h3 class="i-h4 i-reveal" style="margin:40px 0 16px;">{!! $tk($T, 'tlH') !!}</h3>
        <ol class="steps6">
            @foreach($T['en']['steps'] as $i => $st)
                <li class="i-reveal" style="--d:{{ $i * .06 }}s">{!! $tk($T, "steps.$i") !!}</li>
            @endforeach
        </ol>
        @if(!empty($c['history']))
        <details class="i-card i-reveal" style="margin-top:34px;padding:20px 24px;">
            <summary style="cursor:pointer;font-weight:700">{!! $tm($c['history']['title']) !!}</summary>
            <p class="text-sm text-gray-500" style="margin:10px 0 18px">{!! $tm($c['history']['note']) !!}</p>
            <div class="grid md:grid-cols-3 gap-6">
                @foreach($c['history']['rows'] as $row)
                    <div>
                        <h4 class="i-h4" style="margin-bottom:10px">{!! $tk($T, 'school') !!} {{ $row['school'] }}</h4>
                        <ol class="text-sm text-gray-600" style="display:grid;gap:12px;">
                            @foreach($row['steps'] as $st)
                                <li><span class="i-badge">{{ $st['when'] }}</span><br><strong>{!! $tm($st['dates']) !!}</strong> · {!! $tm($st['place']) !!}<br>{!! $tk($T, 'deadline') !!} {!! $tm($st['deadline']) !!}</li>
                            @endforeach
                        </ol>
                    </div>
                @endforeach
            </div>
        </details>
        @endif
    </div>
</section>

{{-- ───────────── GRANT ───────────── --}}
<section class="i-section">
    <div class="i-container">
        <div class="i-card i-card--spectrum i-reveal" style="padding:40px;">
            <div class="grid md:grid-cols-[auto_1fr] gap-8 items-start">
                <div class="i-card__icon i-card__icon--yellow" style="width:68px;height:68px;border-radius:22px;margin:0;">{!! $icon('award', 'w-8 h-8') !!}</div>
                <div>
                    <h2 class="i-h2" style="font-size:1.9rem">{!! $tk($T, 'grantH') !!}</h2>
                    {!! $pk($T, 'grant', 'text-gray-600 leading-relaxed mt-3') !!}
                    <div class="flex flex-wrap gap-3 mt-6">
                        <a class="i-btn i-btn--secondary" href="{{ asset('files/academy-2025-grants-ru.pdf') }}" target="_blank" rel="noopener">{!! $icon('book', 'w-4 h-4') !!}<span>{!! $t('Положение (RU)', 'Regulation (RU)', 'Regulament (RU)') !!}</span></a>
                        <a class="i-btn i-btn--secondary" href="{{ asset('files/academy-2025-grants-en.pdf') }}" target="_blank" rel="noopener">{!! $icon('book', 'w-4 h-4') !!}<span>{!! $t('Положение (EN)', 'Regulation (EN)', 'Regulament (EN)') !!}</span></a>
                        <a class="i-btn i-btn--secondary" href="{{ asset('files/academy-2025-grants-ro.pdf') }}" target="_blank" rel="noopener">{!! $icon('book', 'w-4 h-4') !!}<span>{!! $t('Положение (RO)', 'Regulation (RO)', 'Regulament (RO)') !!}</span></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ───────────── ALUMNI ───────────── --}}
<section class="i-section i-section--soft">
    <div class="i-container">
        <h2 class="i-h2 i-reveal">{!! $tk($T, 'alH') !!}</h2>
        <div class="i-spectrum-line i-reveal" style="width:84px;margin:14px 0 18px;"></div>
        {!! $pk($T, 'alLead', 'i-lead i-reveal') !!}
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5 mt-9">
            @foreach($T['en']['alumni'] as $i => $a)
                <div class="i-card i-card--hover i-reveal" style="--d:{{ ($i % 4) * .06 }}s">
                    <h3 class="i-h4" style="margin-bottom:6px;">{!! $tk($T, "alumni.$i.0") !!}</h3>
                    <p class="text-sm text-gray-500">{!! $tk($T, "alumni.$i.1") !!}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ───────────── EXPERTS TEASER ───────────── --}}
<section class="i-section">
    <div class="i-container">
        <div class="flex flex-wrap items-end justify-between gap-4 mb-9">
            <div class="i-reveal"><h2 class="i-h2">{!! $t('Эксперты-наставники', 'Expert mentors', 'Mentori experți') !!}</h2><div class="i-spectrum-line" style="width:84px;margin-top:14px;"></div></div>
            <a href="{{ route('experts') }}" class="i-btn i-btn--secondary i-reveal"><span>{!! $t('Все эксперты', 'All experts', 'Toți experții') !!}</span>{!! $arrow !!}</a>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-5">
            @foreach(array_slice($experts25, 0, 12) as $i => $e)
                <article class="i-card i-card--hover person i-reveal" style="--d:{{ ($i % 6) * .05 }}s">
                    <div class="ph"><img src="{{ $img($e['img']) }}" alt="{{ $e['name']['en'] }}" loading="lazy" width="112" height="112"></div>
                    <h3>{!! $tm($e['name']) !!}</h3>
                    <p>{!! $tm($e['role']) !!}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>

{{-- ───────────── VIDEO ───────────── --}}
<section class="i-section" id="video">
    <div class="i-container">
        <div class="mb-9 i-reveal"><h2 class="i-h2">{!! $t('Видео', 'Video', 'Video') !!}</h2><div class="i-spectrum-line" style="width:84px;margin-top:14px;"></div></div>
        @include('public._videos', ['videos' => $videos])
        @if(!empty($c['albums']))
        <h3 class="i-h4 i-reveal" style="margin:40px 0 12px;">{!! $t('Фотоальбомы на прежнем сайте', 'Photo albums on the former site', 'Albume foto de pe fostul site') !!}</h3>
        <div class="flex flex-wrap gap-2 i-reveal">
            @foreach($c['albums'] as $al)<span class="i-chip">{!! $tm($al) !!}</span>@endforeach
        </div>
        {!! $p('Сами фотографии 2025 года не сохранились в веб-архиве.', 'The 2025 photos themselves were not preserved in the web archive.', 'Fotografiile din 2025 nu s-au păstrat în arhiva web.', 'text-sm text-gray-500 mt-3 i-reveal') !!}
        @endif
    </div>
</section>

{{-- ───────────── PARTNERS ───────────── --}}
<section class="i-section i-section--soft">
    <div class="i-container">
        <h2 class="i-h2 i-reveal" style="text-align:center">{!! $t('Организаторы и партнёры', 'Organisers and partners', 'Organizatori și parteneri') !!}</h2>
        <div class="i-spectrum-line i-reveal" style="width:84px;margin:14px auto 30px;"></div>
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-4">
            @foreach($partners25 as $j => $pt)
                @if($pt['url'])
                    <a class="logo-tile i-reveal" style="--d:{{ ($j % 6) * .04 }}s" href="{{ $pt['url'] }}" target="_blank" rel="noopener" title="{{ $pt['name'] }}"><img src="{{ $img($pt['img']) }}" alt="{{ $pt['name'] }}" loading="lazy"></a>
                @else
                    <span class="logo-tile i-reveal" style="--d:{{ ($j % 6) * .04 }}s" title="{{ $pt['name'] }}"><img src="{{ $img($pt['img']) }}" alt="{{ $pt['name'] }}" loading="lazy"></span>
                @endif
            @endforeach
        </div>
    </div>
</section>

{{-- ───────────── CTA ───────────── --}}
<section class="i-section">
    <div class="i-container">
        <div class="i-airy i-reveal" style="border-radius:var(--r-2xl);border:1px solid var(--line);padding:60px 28px;text-align:center;box-shadow:var(--sh-md);">
            <h2 class="i-h2" style="max-width:640px;margin:0 auto 14px;">{!! $t('Готовы вести за собой?', 'Ready to lead?', 'Ești gata să conduci?') !!}</h2>
            {!! $p('Выберите школу и подайте заявку до окончания срока.', 'Choose your school and apply before the deadline.', 'Alege școala și depune cererea înainte de termen.', 'i-lead mx-auto mb-8') !!}
            <a href="#deadlines" class="i-btn i-btn--lg">{!! $t('Регистрация', 'Register', 'Înregistrare') !!}</a>
        </div>
    </div>
</section>
@endsection
