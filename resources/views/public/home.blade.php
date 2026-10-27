@extends('public.layout')

@section('title', 'INSPIRE EDU Network — Inter-University Network for Sustainable Peace, Innovation, Research, and Education')
@section('description', 'INSPIRE EDU Network connects universities, young leaders, researchers and international partners in Moldova. Connecting Minds. Building Trust. Creating Change.')

@php
    $tones = ['#006de0', '#ff6c0b', '#3d932e', '#72299c', '#d99b00', '#e31e27', '#00387a'];
    $emojiTone = ['', 'yellow', 'green', 'orange', 'purple', 'red'];
    $expertsWithPhoto = array_values(array_filter($people, fn ($x) => $x['img']));
    $orgs = array_values(array_filter($partners, fn ($x) => in_array($x['group'], ['donor', 'coorg'])));
    $S = $site;
@endphp

@section('content')

{{-- ───────────── HERO: photo backdrop ───────────── --}}
<section class="hero-photo i-airy">
    <div class="i-nodes" aria-hidden="true">
        <svg viewBox="0 0 1200 700" preserveAspectRatio="xMidYMid slice">
            <line x1="840" y1="200" x2="990" y2="280"/><line x1="990" y1="280" x2="1130" y2="200"/><line x1="990" y1="280" x2="1060" y2="360"/>
            <line x1="1060" y1="360" x2="1140" y2="450"/><line x1="900" y1="430" x2="1010" y2="540"/><line x1="1010" y1="540" x2="1140" y2="450"/><line x1="1010" y1="540" x2="960" y2="640"/><line x1="1060" y1="360" x2="900" y2="430"/>
            <circle cx="840" cy="200" r="8" fill="#ff6c0b"/><circle cx="990" cy="280" r="10" fill="#019eff"/><circle cx="1130" cy="200" r="7" fill="#3d932e"/><circle cx="1060" cy="360" r="8" fill="#e31e27"/>
            <circle cx="1140" cy="450" r="9" fill="#fdba07"/><circle cx="900" cy="430" r="7" fill="#72299c"/><circle cx="1010" cy="540" r="11" fill="#006de0"/><circle cx="960" cy="640" r="7" fill="#ff6c0b"/>
        </svg>
    </div>
    <div class="i-nodes hero-edge" aria-hidden="true">
        <span class="pic-spot pic-yellow" style="--c:#fdba07;width:107px;aspect-ratio:1;left:-40px;top:4%"></span>
        <span class="pic-spot pic-sky" style="--c:#019eff;width:340px;aspect-ratio:1;left:-130px;bottom:-4%"></span>
        <svg viewBox="0 0 150 560" width="150" height="560">
            <line x1="36" y1="60" x2="104" y2="140"/><line x1="104" y1="140" x2="60" y2="235"/><line x1="104" y1="140" x2="112" y2="320"/><line x1="60" y1="235" x2="112" y2="320"/>
            <line x1="60" y1="235" x2="30" y2="370"/><line x1="112" y1="320" x2="80" y2="450"/><line x1="30" y1="370" x2="80" y2="450"/><line x1="80" y1="450" x2="118" y2="520"/>
            <circle cx="36" cy="60" r="9" fill="#019eff"/><circle cx="104" cy="140" r="11" fill="#ff6c0b"/><circle cx="60" cy="235" r="8" fill="#72299c"/><circle cx="112" cy="320" r="10" fill="#3d932e"/>
            <circle cx="30" cy="370" r="7" fill="#fdba07"/><circle cx="80" cy="450" r="11" fill="#006de0"/><circle cx="118" cy="520" r="7" fill="#e31e27"/>
        </svg>
    </div>
    <div class="i-container grid lg:grid-cols-[.88fr_1.12fr] gap-8 items-center">
        <div>
            <span class="i-chip i-in i-in-1"><span class="i-dot i-pulse-dot"></span>INSPIRE EDU Network &middot; Moldova</span>
            <h1 class="i-h1 i-in i-in-2" style="margin:20px 0 0;" aria-label="{{ $S['hero']['title']['en'] }}"><span class="hero-main i-grad-text">{!! $tm($S['hero']['title_main']) !!}</span><span class="hero-sub">{!! $tm($S['hero']['title_sub']) !!}</span></h1>
            <p class="hero-tagline i-grad-text i-in i-in-3">{!! $tm($S['hero']['tagline']) !!}</p>
            <div class="flex flex-wrap items-center gap-4 mt-9 i-in i-in-4">
                <a href="{{ $bot }}" target="_blank" rel="noopener" class="i-btn i-btn--lg">{!! $tg !!}<span>{!! $t('Присоединиться', 'Join now', 'Alătură-te') !!}</span></a>
                <a href="{{ route('academy2025') }}" class="i-btn i-btn--secondary i-btn--lg"><span>{!! $t('Мероприятия', 'Events', 'Evenimente') !!}</span>{!! $arrow !!}</a>
            </div>
        </div>
        <div class="hero-pic i-in i-in-3">
            <span class="pic-spot pic-sky" style="--c:#019eff;width:46%;aspect-ratio:1;left:-6%;bottom:-8%"></span>
            <span class="pic-spot pic-yellow" style="--c:#fdba07;width:34%;aspect-ratio:1;left:14%;top:-10%"></span>
            <span class="pic-spot pic-orange" style="--c:#ff6c0b;width:38%;aspect-ratio:1;right:-4%;top:-12%"></span>
            <span class="pic-spot pic-purple" style="--c:#72299c;width:30%;aspect-ratio:1;left:-10%;top:24%"></span>
            <span class="pic-spot pic-green" style="--c:#3d932e;width:34%;aspect-ratio:1;right:8%;bottom:-14%"></span>
            <img src="{{ $img('hero/group-soft.webp') }}" srcset="{{ $img('hero/group-soft-800.webp') }} 800w, {{ $img('hero/group-soft.webp') }} 1400w" sizes="(min-width:1024px) 58vw, 100vw" alt="INSPIRE Leadership Academy 2025 participants" width="1400" height="787" fetchpriority="high" decoding="async">
        </div>
    </div>
</section>
{{-- ───────────── TICKER ───────────── --}}
<div class="ticker" aria-label="Key topics">
    <div class="ticker__track">
        @for($rep = 0; $rep < 4; $rep++)
            @foreach($S['ticker'] as $it)
                <span class="ticker__item" @if($rep > 1) aria-hidden="true" @endif><i></i>{!! $tm($it) !!}</span>
            @endforeach
        @endfor
    </div>
</div>

{{-- ───────────── INSPIRE = 7 letters ───────────── --}}
<section class="i-section" id="inspire">
    <div class="i-container">
        <div class="text-center max-w-3xl mx-auto mb-14 i-reveal">
            <p class="i-eyebrow">INSPIRE EDU Network</p>
            <h2 class="i-h2" style="margin-top:10px;">{!! $tm($S['acronym']['title']) !!}</h2>
            <div class="i-spectrum-line" style="width:84px;margin:16px auto 0;"></div>
        </div>
        <div class="acr-grid">
            @foreach($S['acronym']['items'] as $i => $a)
                <article class="acr-card i-reveal" style="--c:{{ $tones[$i % count($tones)] }};--d:{{ ($i % 4) * .06 }}s">
                    <div class="letter" aria-hidden="true">{{ $a['letter'] }}</div>
                    <h3>{!! $tm($a['name']) !!}</h3>
                    <p>{!! $tm($a['text']) !!}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>

{{-- ───────────── OUR STORY ───────────── --}}
<section class="i-section i-section--tint" id="story">
    <div class="i-container">
        <div class="grid lg:grid-cols-[1.25fr_.75fr] gap-12 items-start">
            <div class="story">
                <h2 class="i-h2 i-reveal">{!! $tm($S['story']['title']) !!}</h2>
                <div class="i-spectrum-line i-reveal" style="width:84px;margin:14px 0 28px;"></div>
                @foreach($S['story']['paras'] as $i => $par)
                    <div class="i-reveal" style="--d:{{ $i * .04 }}s">{!! $pm($par) !!}</div>
                @endforeach
                <div class="i-reveal"><p style="font-weight:700;color:var(--navy-800)">{!! $tm($S['story']['closing']) !!}</p></div>
                <a class="funding i-reveal" href="{{ $S['story']['funding_url'] }}" target="_blank" rel="noopener">
                    <img src="{{ $img('partners/embassy-nl.png') }}" alt="Kingdom of the Netherlands" loading="lazy">
                    <span>{!! $tm($S['story']['funding']) !!}</span>
                </a>
            </div>
            <div class="grid grid-cols-2 gap-4 lg:sticky" style="top:100px;">
                @foreach($S['story']['stats'] as $i => $st)
                    <div class="i-card story-stat i-reveal" style="--d:{{ $i * .07 }}s">
                        <div class="num-big i-grad-text stat-num">{{ $st['n'] }}</div>
                        <p class="i-eyebrow" style="margin-top:8px;color:var(--muted);letter-spacing:.06em;text-transform:none;font-size:.8rem">{!! $tm($st['label']) !!}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- ───────────── OUR VALUES ───────────── --}}
<section class="i-section" id="values">
    <div class="i-container">
        <div class="max-w-3xl mb-12 i-reveal">
            <h2 class="i-h2">{!! $tm($S['values']['title']) !!}</h2>
            <div class="i-spectrum-line" style="width:84px;margin:14px 0 20px;"></div>
            {!! $pm($S['values']['intro'], 'i-lead') !!}
        </div>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($S['values']['items'] as $i => $v)
                <div class="i-card i-card--hover i-reveal" style="--d:{{ ($i % 3) * .07 }}s">
                    <h3 class="i-h4" style="margin-bottom:8px">{!! $tm($v['name']) !!}</h3>
                    {!! $pm($v['text'], 'text-sm text-gray-500 leading-relaxed') !!}
                </div>
            @endforeach
        </div>
        <div class="vision mt-10 i-reveal">
            <p class="i-eyebrow" style="color:#fff;opacity:.85;margin-bottom:8px">{!! $tm($S['values']['vision_label']) !!}</p>
            {!! $pm($S['values']['vision']) !!}
        </div>
    </div>
</section>

{{-- ───────────── VALUES & CORE AREAS ───────────── --}}
<section class="i-section" style="padding-top:0">
    <div class="i-container">
        @php
            $cloud = [
                ['2.25rem', '#006de0'], ['1.7rem', '#3d932e'], ['2.6rem', '#ff6c0b'], ['1.9rem', '#72299c'],
                ['1.55rem', '#006de0'], ['1.85rem', '#e31e27'], ['2.15rem', '#00387a'], ['1.6rem', '#3d932e'],
            ];
        @endphp
        <div class="core-panel i-reveal">
            <span class="deco" style="width:54px;height:54px;border-color:#0682e4;left:46%;top:8%;opacity:.7" aria-hidden="true"></span>
            <span class="deco" style="width:20px;height:20px;border-color:#e31e27;left:50%;bottom:10%;opacity:.6" aria-hidden="true"></span>
            <span class="deco" style="width:38px;height:38px;border-color:#e31e27;right:5%;top:6%;border-radius:10px;transform:rotate(45deg);opacity:.5" aria-hidden="true"></span>
            <div>
                <h2 class="i-h2">{!! $tm($S['core']['title']) !!}</h2>
                <div class="bar"></div>
                {!! $pm($S['core']['text'], 'text-gray-700 leading-relaxed') !!}
            </div>
            <div class="cloud" aria-label="Core areas">
                @foreach($S['core']['phrases'] as $i => $ph)
                    <span style="font-size:{{ $cloud[$i][0] }};color:{{ $cloud[$i][1] }}">{!! $tm($ph) !!}</span>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- ───────────── EVENTS ───────────── --}}
<section class="i-section i-section--soft" id="events">
    <div class="i-container">
        <div class="flex flex-wrap items-end justify-between gap-4 mb-10">
            <div class="i-reveal"><h2 class="i-h2">{!! $t('Мероприятия', 'Events', 'Evenimente') !!}</h2><div class="i-spectrum-line" style="width:84px;margin-top:14px;"></div></div>
            <a href="{{ route('academy2025') }}" class="i-btn i-btn--secondary i-reveal"><span>{!! $t('Школы 2025', 'Schools 2025', 'Școli 2025') !!}</span>{!! $arrow !!}</a>
        </div>
        @php
            $E = $S['events'];
            $cards = [
                ['badge' => ['ru' => 'Скоро', 'en' => 'Coming soon', 'ro' => 'În curând'], 'title' => $S['hackathon']['title'], 'dates' => ['ru' => '2026', 'en' => '2026', 'ro' => '2026'], 'place' => null, 'url' => route('hackathon'), 'tone' => 'orange'],
                ['badge' => ['ru' => 'Итоговая конференция', 'en' => 'Final Conference', 'ro' => 'Conferința finală'], 'title' => $E['final']['title'], 'dates' => $E['final']['date'], 'place' => $E['final']['place'], 'url' => route('academy2025') . '#final', 'tone' => 'purple'],
            ];
            foreach ($E['schools2025']['items'] as $sc) {
                $cards[] = ['badge' => ['ru' => 'Школа ' . $sc['n'], 'en' => 'School ' . $sc['n'], 'ro' => 'Școala ' . $sc['n']], 'title' => $sc['title'], 'dates' => $sc['dates'], 'place' => array_map(fn ($v) => preg_replace('/^.*\s\/\s/u', '', $v), $sc['place']), 'url' => route('academy2025.school', $sc['n']), 'tone' => ['green', 'yellow', 'blue'][$sc['n'] - 1]];
            }
            $cards[] = ['badge' => ['ru' => 'Архив 2023', 'en' => 'Archive 2023', 'ro' => 'Arhivă 2023'], 'title' => $E['school2023']['title'], 'dates' => $E['school2023']['dates'], 'place' => array_map(fn ($v) => preg_replace(['/^Комплекс\s+/u', '/^Complexul\s+/u', '/\s+Complex\b/u'], '', $v), $E['school2023']['place']), 'url' => route('academy2023'), 'tone' => 'red'];
        @endphp
        @php $evColor = ['orange' => '#ff6c0b', 'purple' => '#72299c', 'green' => '#3d932e', 'yellow' => '#d99b00', 'blue' => '#006de0', 'red' => '#e31e27']; @endphp
        {{-- agenda list: the date leads (largest), then type + title, then place; one row per event --}}
        @php
            $splitDate = function (array $d): array {   // "15–17 мая 2025" -> ['15–17 мая', '2025']
                $out = [];
                foreach ($d as $lang => $v) {
                    $out[$lang] = preg_match('/^(.*?)\s*(\d{4})$/u', $v, $m) && trim($m[1]) !== '' ? [trim($m[1]), $m[2]] : [$v, ''];
                }
                return $out;
            };
        @endphp
        <div class="ev-list">
            @foreach($cards as $i => $ev)
                @php $dt = $splitDate($ev['dates']); @endphp
                <a href="{{ $ev['url'] }}" class="ev3 i-reveal" style="--d:{{ ($i % 3) * .05 }}s;--c:{{ $evColor[$ev['tone']] }}">
                    <div class="ev3__date">
                        @foreach(['ru', 'en', 'ro'] as $l)<span data-lang="{{ $l }}"><b>{{ $dt[$l][0] }}</b>@if($dt[$l][1])<em>{{ $dt[$l][1] }}</em>@endif</span>@endforeach
                    </div>
                    <div class="ev3__body">
                        <span class="ev3__type">{!! $tm($ev['badge']) !!}</span>
                        <h3 class="ev3__title">{!! $tm($ev['title']) !!}</h3>
                    </div>
                    <p class="ev3__place">
                        @if($ev['place']){!! $icon('pin', 'w-4 h-4') !!}<span>{!! $tm($ev['place']) !!}</span>@else<span>{!! $t('Подробности скоро', 'Details coming soon', 'Detalii în curând') !!}</span>@endif
                    </p>
                    <span class="ev3__go" aria-hidden="true">{!! $arrow !!}</span>
                </a>
            @endforeach
        </div>
    </div>
</section>

{{-- ───────────── EXPERT MENTORS & ADVISORS (block unchanged) ───────────── --}}
<section class="i-section">
    <div class="i-container">
        <div class="flex flex-wrap items-end justify-between gap-4 mb-9">
            <div class="i-reveal"><h2 class="i-h2">{!! $t('Эксперты и наставники', 'Expert Mentors & Advisors', 'Experți și mentori') !!}</h2><div class="i-spectrum-line" style="width:84px;margin-top:14px;"></div></div>
            <a href="{{ route('experts') }}" class="i-btn i-btn--secondary i-reveal"><span>{!! $t('Все эксперты', 'All experts', 'Toți experții') !!} ({{ count($people) }})</span>{!! $arrow !!}</a>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-5">
            @foreach(array_slice($expertsWithPhoto, 0, 12) as $i => $e)
                <article class="i-card i-card--hover person i-reveal" style="--d:{{ ($i % 6) * .05 }}s">
                    <div class="ph"><img src="{{ $img($e['img']) }}" alt="{{ $e['name']['en'] }}" loading="lazy" width="112" height="112"></div>
                    <h3>{!! $tm($e['name']) !!}</h3>
                    <p>{!! $tm($e['role']) !!}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>

{{-- ───────────── WHO CAN JOIN ───────────── --}}
<section class="i-section i-section--tint" id="who">
    <div class="i-container">
        <div class="max-w-3xl mb-12 i-reveal">
            <h2 class="i-h2">{!! $tm($S['who']['title']) !!}</h2>
            <div class="i-spectrum-line" style="width:84px;margin:14px 0 20px;"></div>
            {!! $pm($S['who']['intro'], 'i-lead') !!}
            <p class="mt-5 font-bold text-ink">{!! $tm($S['who']['lead']) !!}</p>
        </div>
        {{-- five audience cards: 3 + 2 (wider) so every row is filled and balanced; the language note is its own band below --}}
        @php
            $spans = ['md:col-span-3 lg:col-span-2', 'md:col-span-3 lg:col-span-2', 'md:col-span-6 lg:col-span-2', 'md:col-span-3 lg:col-span-3', 'md:col-span-3 lg:col-span-3'];
            $accent = ['#006de0', '#ff6c0b', '#3d932e', '#72299c', '#d99b00'];
        @endphp
        <div class="grid grid-cols-1 md:grid-cols-6 gap-6">
            @foreach($S['who']['items'] as $i => $w)
                <div class="i-card i-card--hover i-reveal {{ $spans[$i] }}" style="--d:{{ ($i % 3) * .07 }}s;border-top:4px solid {{ $accent[$i] }};display:flex;flex-direction:column;">
                    <h3 class="i-h4" style="margin-bottom:8px">{!! $tm($w['name']) !!}</h3>
                    {!! $pm($w['text'], 'text-sm text-gray-500 leading-relaxed') !!}
                </div>
            @endforeach
        </div>
        <div class="i-card i-card--spectrum i-reveal mt-6 grid md:grid-cols-[300px_1fr] gap-4 md:gap-10 items-center" style="--d:.15s;padding:26px 32px;">
            <h3 class="i-h4">{!! $tm($S['who']['lang_title']) !!}</h3>
            {!! $pm($S['who']['lang_text'], 'text-sm text-gray-600 leading-relaxed') !!}
        </div>
    </div>
</section>

{{-- ───────────── KEY FEATURES ───────────── --}}
<section class="i-section" id="features">
    <div class="i-container">
        <div class="mb-12 i-reveal">
            <h2 class="i-h2">{!! $tm($S['features']['title']) !!}</h2>
            <div class="i-spectrum-line" style="width:84px;margin-top:14px;"></div>
        </div>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-x-10 gap-y-9">
            @foreach($S['features']['items'] as $i => $f)
                <div class="feat i-reveal" style="--d:{{ ($i % 3) * .07 }}s">
                    <span class="ico" style="color:{{ $tones[$i % count($tones)] }}">{!! $icon($f['icon'], 'w-7 h-7') !!}</span>
                    <div>
                        <h3>{!! $tm($f['name']) !!}</h3>
                        {!! $pm($f['text']) !!}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ───────────── ALUMNI ───────────── --}}
<section class="i-section i-section--soft" id="alumni">
    <div class="i-container">
        <div class="max-w-3xl mb-12 i-reveal">
            <h2 class="i-h2">{!! $tm($S['alumni']['title']) !!}</h2>
            <div class="i-spectrum-line" style="width:84px;margin:14px 0 20px;"></div>
            {!! $pm($S['alumni']['intro'], 'i-lead') !!}
        </div>
        {{-- seven benefits: 4 + 3 (wider) so both rows are full; the closing sentence is calm text, not a loud card --}}
        @php $aSpan = ['sm:col-span-3 lg:col-span-3', 'sm:col-span-3 lg:col-span-3', 'sm:col-span-3 lg:col-span-3', 'sm:col-span-3 lg:col-span-3', 'sm:col-span-3 lg:col-span-4', 'sm:col-span-3 lg:col-span-4', 'sm:col-span-6 lg:col-span-4']; @endphp
        <div class="grid grid-cols-1 sm:grid-cols-6 lg:grid-cols-12 gap-5">
            @foreach($S['alumni']['items'] as $i => $a)
                <div class="i-card i-card--hover i-reveal {{ $aSpan[$i] }}" style="--d:{{ ($i % 4) * .06 }}s">
                    <h3 class="i-h4" style="margin-bottom:6px">{!! $tm($a['name']) !!}</h3>
                    {!! $pm($a['text'], 'text-sm text-gray-500 leading-relaxed') !!}
                </div>
            @endforeach
        </div>
        {{-- closing band: the sentence leads (largest), the invitation is a quiet pill on the right --}}
        <div class="alumni-close i-reveal">
            <div class="alumni-close__text">{!! $pm($S['alumni']['closing']) !!}</div>
            <a href="{{ $bot }}" target="_blank" rel="noopener" class="i-btn i-btn--secondary alumni-close__cta">{!! $tg !!}<span>{!! $t('Присоединиться к сети в Telegram', 'Join the network in Telegram', 'Alătură-te rețelei în Telegram') !!}</span></a>
        </div>
    </div>
</section>

{{-- ───────────── PHOTO ───────────── --}}
<section class="i-section">
    <div class="i-container">
        <div class="flex flex-wrap items-end justify-between gap-4 mb-9">
            <div class="i-reveal"><h2 class="i-h2">{!! $t('Фото', 'Photo', 'Foto') !!}</h2><div class="i-spectrum-line" style="width:84px;margin-top:14px;"></div></div>
            <a href="{{ route('photo') }}" class="i-btn i-btn--secondary i-reveal"><span>{!! $t('Все фотографии', 'All photos', 'Toate fotografiile') !!}</span>{!! $arrow !!}</a>
        </div>
        <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($S['albums'] as $i => $al)
                @php $items = $gallery[$al['key']]; $cover = $al['cover'] < 0 ? $items[count($items) + $al['cover']] : $items[$al['cover']]; @endphp
                <a class="cover-card i-reveal" style="--d:{{ ($i % 3) * .06 }}s" href="{{ route('photo') }}#{{ $al['key'] }}">
                    <img src="{{ $gal($al['key'], $cover['f'], true) }}" alt="{{ $al['title']['en'] }}" loading="lazy" width="{{ min($cover['w'], 520) }}" height="{{ round(min($cover['w'], 520) * $cover['h'] / $cover['w']) }}">
                    <span>{!! $tm($al['title']) !!}<small>{!! $tm($al['sub']) !!}</small></span>
                </a>
            @endforeach
        </div>
    </div>
</section>

{{-- ───────────── VIDEO (existing block, unchanged) ───────────── --}}
<section class="i-section i-section--soft">
    <div class="i-container">
        <div class="flex flex-wrap items-end justify-between gap-4 mb-9">
            <div class="i-reveal"><h2 class="i-h2">{!! $t('Видео', 'Video', 'Video') !!}</h2><div class="i-spectrum-line" style="width:84px;margin-top:14px;"></div></div>
            <a href="{{ route('video') }}" class="i-btn i-btn--secondary i-reveal"><span>{!! $t('Все видео', 'All videos', 'Toate videoclipurile') !!}</span>{!! $arrow !!}</a>
        </div>
        @include('public._videos', ['videos' => $videos])
    </div>
</section>

{{-- ───────────── DONOR (one calm band) + PARTNERS (centred logo wall) ───────────── --}}
@php
    $donors = array_values(array_filter($partners, fn ($x) => $x['group'] === 'donor'));
    $partnerLogos = array_values(array_filter($partners, fn ($x) => $x['group'] !== 'donor'));
@endphp
<section class="i-section">
    <div class="i-container">
        @foreach($donors as $pt)
            <a class="donor-band i-reveal" href="{{ $pt['url'] ?: route('partners') }}" @if($pt['url']) target="_blank" rel="noopener" @endif title="{{ $pt['name'] }}">
                <img src="{{ $img($pt['img']) }}" alt="{{ $pt['name'] }}" loading="lazy">
                <span class="donor-band__text">
                    <b class="i-eyebrow i-grad-text">{!! $t('Донор сети INSPIRE', 'Donor of the INSPIRE network', 'Donatorul rețelei INSPIRE') !!}</b>
                    <span>{!! $t('Посольство Королевства Нидерландов', 'Embassy of the Kingdom of the Netherlands', 'Ambasada Regatului Țărilor de Jos') !!}</span>
                </span>
                <span class="donor-band__go" aria-hidden="true">{!! $arrow !!}</span>
            </a>
        @endforeach
        <div class="logo-wall">
            @foreach($partnerLogos as $j => $pt)
                <a class="logo-tile i-reveal" style="--d:{{ ($j % 6) * .05 }}s" href="{{ $pt['url'] ?: route('partners') }}" @if($pt['url']) target="_blank" rel="noopener" @endif title="{{ $pt['name'] }}"><img src="{{ $img($pt['img']) }}" alt="{{ $pt['name'] }}" loading="lazy"></a>
            @endforeach
        </div>
        <div class="text-center mt-8 i-reveal"><a href="{{ route('partners') }}" class="i-btn i-btn--secondary"><span>{!! $t('Все партнёры', 'All partners', 'Toți partenerii') !!} ({{ count($partners) }})</span>{!! $arrow !!}</a></div>
    </div>
</section>
{{-- ───────────── CTA ───────────── --}}
<section class="i-section" style="padding-top:0">
    <div class="i-container">
        <div class="i-airy i-reveal" style="border-radius:var(--r-2xl);border:1px solid var(--line);padding:68px 28px;text-align:center;box-shadow:var(--sh-md);">
            <img src="{{ asset('brand/logo-mark.png') }}" alt="" width="120" height="120" class="i-float" style="width:104px;height:auto;margin:0 auto 14px;">
            <h2 class="i-h2" style="max-width:700px;margin:0 auto 14px;">{!! $tm($S['hero']['tagline']) !!}</h2>
            {!! $p('Присоединяйтесь к сети INSPIRE в Telegram: мероприятия, эксперты и новые возможности.', 'Join the INSPIRE network in Telegram: events, experts and new opportunities.', 'Alătură-te rețelei INSPIRE în Telegram: evenimente, experți și noi oportunități.', 'i-lead mx-auto mb-8') !!}
            <a href="{{ $bot }}" target="_blank" rel="noopener" class="i-btn i-btn--lg">{!! $tg !!}<span>{!! $t('Присоединиться', 'Join now', 'Alătură-te') !!}</span></a>
        </div>
    </div>
</section>

@endsection

