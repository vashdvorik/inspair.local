@extends('public.layout')

@section('title', 'Schools 2025 — INSPIRE Leadership Academy')
@section('description', 'INSPIRE Leadership Academies 2025: three schools in Chișinău, Comrat and Tiraspol and the Final Conference at Digital Park.')

@php
    $T = $c['t'];
    $E = $site['events'];
    $albumOf = [1 => 'school-1-chisinau', 2 => 'school-2-comrat', 3 => 'school-3-tiraspol'];
    $shots = [1 => [0, 8, 20], 2 => [0, 6, 18], 3 => [-1, 10, 30]];
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
                <span>{!! $icon('cal', 'w-5 h-5') !!}{!! $t('Май — декабрь 2025', 'May — December 2025', 'Mai — decembrie 2025') !!}</span>
                <span>{!! $icon('pin', 'w-5 h-5') !!}{!! $t('Кишинёв · Комрат · Тирасполь', 'Chisinau · Comrat · Tiraspol', 'Chișinău · Comrat · Tiraspol') !!}</span>
            </div>
            <div class="flex flex-wrap gap-4 mt-8 i-in i-in-5">
                <a href="#schools" class="i-btn i-btn--lg">{!! $t('Смотреть школы', 'Explore the schools', 'Descoperă școlile') !!}</a>
                <a href="#final" class="i-btn i-btn--secondary i-btn--lg"><span>{!! $t('Итоговая конференция', 'Final Conference', 'Conferința finală') !!}</span>{!! $arrow !!}</a>
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

{{-- ───────────── FINAL CONFERENCE ───────────── --}}
<section class="i-section i-section--tint" id="final">
    <div class="i-container">
        @php $F = $E['final']; $fc = $gallery['final-conference']; @endphp
        <div class="grid lg:grid-cols-[1.15fr_.85fr] gap-10 items-start">
            <div>
                <span class="i-badge i-badge--purple i-reveal">{!! $t('Итоговая конференция', 'Final Conference', 'Conferința finală') !!}</span>
                <h2 class="i-h2 i-reveal" style="margin-top:14px">{!! $tm($F['title']) !!}</h2>
                <div class="i-spectrum-line i-reveal" style="width:84px;margin:14px 0 18px;"></div>
                <div class="flex flex-wrap gap-x-6 gap-y-1 text-sm font-semibold text-ink mb-4 i-reveal">
                    <span class="inline-flex items-center gap-1.5">{!! $icon('cal', 'w-4 h-4 text-orange') !!}{!! $tm($F['date']) !!}</span>
                    <span class="inline-flex items-center gap-1.5">{!! $icon('pin', 'w-4 h-4 text-orange') !!}{!! $tm($F['place']) !!}</span>
                </div>
                <div class="i-reveal">{!! $pm($F['format'], 'font-semibold text-ink leading-relaxed mb-3') !!}</div>
                @foreach($F['paras'] as $par)<div class="i-reveal">{!! $pm($par, 'text-gray-600 leading-relaxed mb-3') !!}</div>@endforeach
                <p class="i-eyebrow mt-6 mb-3 i-reveal">{!! $t('Ключевые темы', 'Key Focus Areas', 'Domenii-cheie') !!}</p>
                <div class="areas i-reveal">@foreach($F['areas'] as $ar)<span class="i-chip">{!! $tm($ar) !!}</span>@endforeach</div>
            </div>
            <div class="grid grid-cols-2 gap-3" data-gallery>
                @foreach($fc as $k => $ph)
                    <a class="cover-card i-reveal" style="--d:{{ $k * .06 }}s;aspect-ratio:1/1" href="{{ $gal('final-conference', $ph['f']) }}"><img src="{{ $gal('final-conference', $ph['f'], true) }}" alt="Final Conference {{ $k + 1 }}" loading="lazy" width="{{ $ph['w'] }}" height="{{ $ph['h'] }}"></a>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- ───────────── THREE SCHOOLS ───────────── --}}
<section class="i-section" id="schools">
    <div class="i-container">
        <h2 class="i-h2 i-reveal">{!! $tm($E['schools2025']['title']) !!}</h2>
        <div class="i-spectrum-line i-reveal" style="width:84px;margin:14px 0 40px;"></div>
        <div class="grid gap-10">
            @foreach($E['schools2025']['items'] as $sc)
                @php $n = $sc['n']; $alb = $albumOf[$n]; $items = $gallery[$alb]; $tone = ['', 'yellow', 'green'][$n - 1]; @endphp
                <article class="i-card i-reveal" id="school-{{ $n }}" style="padding:34px">
                    <div class="grid lg:grid-cols-[1.2fr_.8fr] gap-10 items-start">
                        <div>
                            <span class="i-badge {{ $tone ? 'i-badge--' . $tone : '' }}">{!! $t('Школа ' . $n, 'School ' . $n, 'Școala ' . $n) !!}</span>
                            <h3 class="i-h3" style="margin:14px 0 12px">{!! $tm($sc['title']) !!}</h3>
                            <div class="flex flex-wrap gap-x-6 gap-y-1 text-sm font-semibold text-ink mb-4">
                                <span class="inline-flex items-center gap-1.5">{!! $icon('cal', 'w-4 h-4 text-orange') !!}{!! $t('Даты:', 'Dates:', 'Date:') !!} {!! $tm($sc['dates']) !!}</span>
                                <span class="inline-flex items-center gap-1.5">{!! $icon('pin', 'w-4 h-4 text-orange') !!}{!! $tm($sc['place']) !!}</span>
                            </div>
                            {!! $pm($sc['format'], 'font-semibold text-ink leading-relaxed mb-3') !!}
                            @foreach($sc['paras'] as $par){!! $pm($par, 'text-gray-600 leading-relaxed mb-3') !!}@endforeach
                            <p class="i-eyebrow mt-5 mb-3">{!! $t('Ключевые темы', 'Key Focus Areas', 'Domenii-cheie') !!}</p>
                            <div class="areas">@foreach($sc['areas'] as $ar)<span class="i-chip">{!! $tm($ar) !!}</span>@endforeach</div>
                            <div class="flex flex-wrap gap-3 mt-7">
                                <a href="{{ route('academy2025.school', $n) }}" class="i-btn i-btn--secondary i-btn--sm"><span>{!! $t('Программа', 'Programme', 'Program') !!}</span>{!! $arrow !!}</a>
                                <a href="{{ route('photo') }}#{{ $alb }}" class="i-btn i-btn--ghost i-btn--sm"><span>{!! $t('Все фото', 'All photos', 'Toate fotografiile') !!} ({{ count($items) }})</span></a>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3" data-gallery>
                            @foreach($shots[$n] as $k => $ix)
                                @php $ph = $ix < 0 ? $items[count($items) + $ix] : $items[$ix]; @endphp
                                <a class="cover-card" href="{{ $gal($alb, $ph['f']) }}" style="{{ $k === 0 ? 'grid-column:1/-1;aspect-ratio:16/10' : 'aspect-ratio:1/1' }}"><img src="{{ $gal($alb, $ph['f'], true) }}" alt="{{ $sc['title']['en'] }}" loading="lazy" width="{{ $ph['w'] }}" height="{{ $ph['h'] }}"></a>
                            @endforeach
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
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
            {!! $p('Присоединяйтесь к сети INSPIRE в Telegram, чтобы узнавать о новых мероприятиях первыми.', 'Join the INSPIRE network in Telegram to hear about new events first.', 'Alătură-te rețelei INSPIRE în Telegram ca să afli primul despre noile evenimente.', 'i-lead mx-auto mb-8') !!}
            <a href="{{ $bot }}" target="_blank" rel="noopener" class="i-btn i-btn--lg">{!! $tg !!}<span>{!! $t('Присоединиться', 'Join now', 'Alătură-te') !!}</span></a>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="{{ \App\Support\PublicUi::versioned('design/gallery.js') }}" defer></script>
@endpush
