@extends('public.layout')

@section('title', 'INSPIRE EDU Network — Платформа партнёрства Молдовы')
@section('description', 'INSPIRE EDU Network — платформа ИИ-матчинга для молодых лидеров и предпринимателей Молдовы. Найдите партнёра, ментора или соучредителя через Telegram.')

@section('content')

@php
    $T = $c25['t'];
    $avatarTone = ['linear-gradient(135deg,#006de0,#019eff)', 'linear-gradient(135deg,#72299c,#006de0)', 'linear-gradient(135deg,#ff6c0b,#fdba07)'];
    $tones = ['var(--purple)', 'var(--blue-600)', 'var(--orange)', 'var(--green)'];
    // all schools, oldest first: Summer School 2023 + the three schools of 2025
    $schools = [[
        'year' => 2023, 'dates' => $c23['dates'],
        'title' => ['ru' => $c23['title'], 'en' => $c23['title'], 'ro' => $c23['title']],
        'place' => ['ru' => 'Молдова', 'en' => 'Moldova', 'ro' => 'Moldova'],
        'topics' => array_slice($c23['programme'], 2, 4),
        'url' => route('academy2023'),
    ]];
    foreach ($T['en']['schools'] as $i => $sc) {
        $schools[] = [
            'year' => 2025,
            'dates' => ['ru' => $T['ru']['schools'][$i]['dates'], 'en' => $T['en']['schools'][$i]['dates'], 'ro' => $T['ro']['schools'][$i]['dates']],
            'title' => ['ru' => $T['ru']['schools'][$i]['title'], 'en' => $T['en']['schools'][$i]['title'], 'ro' => $T['ro']['schools'][$i]['title']],
            'place' => ['ru' => $T['ru']['schools'][$i]['place'], 'en' => $T['en']['schools'][$i]['place'], 'ro' => $T['ro']['schools'][$i]['place']],
            'topics' => array_map(fn ($j) => ['ru' => $T['ru']['schools'][$i]['areas'][$j], 'en' => $T['en']['schools'][$i]['areas'][$j], 'ro' => $T['ro']['schools'][$i]['areas'][$j]], [0, 1, 2, 3]),
            'url' => route('academy2025.school', $i + 1),
        ];
    }
    $expertsWithPhoto = array_values(array_filter($people, fn ($x) => $x['img']));
    $orgs = array_values(array_filter($partners, fn ($x) => in_array($x['group'], ['donor', 'coorg'])));
@endphp

{{-- ───────────── HERO ───────────── --}}
<section class="i-airy" style="padding: 138px 0 96px;">
    <div class="i-nodes" aria-hidden="true">
        <svg viewBox="0 0 1200 700" preserveAspectRatio="xMidYMid slice">
            <line x1="60" y1="560" x2="210" y2="420"/><line x1="210" y1="420" x2="380" y2="500"/><line x1="380" y1="500" x2="520" y2="610"/>
            <line x1="840" y1="90" x2="990" y2="170"/><line x1="990" y1="170" x2="1130" y2="90"/><line x1="990" y1="170" x2="1060" y2="300"/>
            <circle cx="60" cy="560" r="9" fill="#006de0"/><circle cx="210" cy="420" r="11" fill="#fdba07"/><circle cx="380" cy="500" r="8" fill="#72299c"/><circle cx="520" cy="610" r="7" fill="#3d932e"/>
            <circle cx="840" cy="90" r="8" fill="#ff6c0b"/><circle cx="990" cy="170" r="10" fill="#019eff"/><circle cx="1130" cy="90" r="7" fill="#3d932e"/><circle cx="1060" cy="300" r="8" fill="#e31e27"/>
        </svg>
    </div>
    <div class="i-container grid lg:grid-cols-2 gap-14 items-center">
        <div>
            <span class="i-chip i-in i-in-1"><span class="i-dot i-pulse-dot"></span>INSPIRE EDU Network &middot; Moldova</span>
            <h1 class="i-h1 i-in i-in-2" style="margin:22px 0 22px;">
                <span data-lang="ru">Платформа<br><span class="i-grad-text">партнёрства</span><br>и роста</span>
                <span data-lang="en">Platform of<br><span class="i-grad-text">partnership</span><br>and growth</span>
                <span data-lang="ro">Platformă de<br><span class="i-grad-text">parteneriat</span><br>și creștere</span>
            </h1>
            {!! $p('Объединяем молодых лидеров и предпринимателей Молдовы через технологии ИИ-матчинга.', 'Connecting young leaders and entrepreneurs of Moldova through AI matching technology.', 'Unim tineri lideri și antreprenori ai Moldovei prin tehnologia de matching AI.', 'i-lead i-in i-in-3') !!}
            <div class="flex flex-wrap items-center gap-4 mt-9 i-in i-in-4">
                <a href="{{ $bot }}" target="_blank" rel="noopener" class="i-btn i-btn--lg">{!! $tg !!}<span>{!! $t('Присоединиться', 'Join now', 'Alătură-te') !!}</span></a>
                <a href="#schools" class="i-btn i-btn--secondary i-btn--lg"><span>{!! $t('Школы и академии', 'Schools and academies', 'Școli și academii') !!}</span>{!! $arrow !!}</a>
            </div>
            <div class="flex flex-wrap gap-2.5 mt-9 i-in i-in-5">
                <span class="i-chip">{!! $t('Бесплатно', 'Free', 'Gratuit') !!}</span>
                <span class="i-chip">{!! $t('Через Telegram', 'Via Telegram', 'Prin Telegram') !!}</span>
                <span class="i-chip">RU · EN · RO</span>
            </div>
        </div>
        <div class="i-in i-in-3" aria-hidden="true">
            <div class="hero-art">
                <div class="ring ring--1"><i style="top:-8px;left:46%;background:var(--yellow)"></i><i style="bottom:12%;right:3%;background:var(--purple)"></i><i style="top:30%;left:-8px;background:var(--blue-600)"></i></div>
                <div class="ring ring--2"><i style="top:-8px;left:20%;background:var(--green)"></i><i style="bottom:-8px;left:58%;background:var(--orange)"></i></div>
                <div class="ring ring--3"><i style="top:50%;right:-8px;background:var(--sky)"></i><i style="bottom:6%;left:14%;background:var(--red)"></i></div>
                <div class="core i-float"><img src="{{ asset('brand/logo-mark.png') }}" alt="" width="300" height="300"></div>
                <div class="tile i-float" style="top:2%;right:10%;animation-delay:-1s;color:var(--blue-600)">{!! $icon('group', 'w-7 h-7') !!}</div>
                <div class="tile i-float" style="bottom:6%;left:8%;animation-delay:-3s;color:var(--green)">{!! $icon('leaf', 'w-7 h-7') !!}</div>
                <div class="tile i-float" style="top:46%;left:-3%;animation-delay:-5s;color:#c98f00">{!! $icon('bolt', 'w-7 h-7') !!}</div>
                <div class="tile i-float" style="bottom:20%;right:-2%;animation-delay:-2s;color:var(--purple)">{!! $icon('globe', 'w-7 h-7') !!}</div>
            </div>
        </div>
    </div>
</section>

{{-- ───────────── STATS ───────────── --}}
<section class="relative z-10" style="margin-top:-34px;">
    <div class="i-container">
        <div class="i-card grid grid-cols-2 md:grid-cols-4 gap-6 text-center" style="padding:30px 24px;box-shadow:var(--sh-md);">
            <div class="i-reveal"><div class="num-big i-grad-text stat-num">{{ count($schools) }}</div><p class="i-eyebrow" style="margin-top:8px;color:var(--muted)">{!! $t('школы INSPIRE', 'INSPIRE schools', 'școli INSPIRE') !!}</p></div>
            <div class="i-reveal" style="--d:.08s"><div class="num-big i-grad-text stat-num">{{ count($people) }}</div><p class="i-eyebrow" style="margin-top:8px;color:var(--muted)">{!! $t('экспертов', 'experts', 'experți') !!}</p></div>
            <div class="i-reveal" style="--d:.16s"><div class="num-big i-grad-text stat-num">{{ count($partners) }}</div><p class="i-eyebrow" style="margin-top:8px;color:var(--muted)">{!! $t('партнёров', 'partners', 'parteneri') !!}</p></div>
            <div class="i-reveal" style="--d:.24s"><div class="num-big i-grad-text stat-num">3</div><p class="i-eyebrow" style="margin-top:8px;color:var(--muted)">{!! $t('города: Кишинёв, Комрат, Тирасполь', 'cities: Chisinau, Comrat, Tiraspol', 'orașe: Chișinău, Comrat, Tiraspol') !!}</p></div>
        </div>
    </div>
</section>

{{-- ───────────── ABOUT ───────────── --}}
<section class="i-section" id="about">
    <div class="i-container">
        <div class="text-center max-w-2xl mx-auto mb-14 i-reveal">
            <p class="i-eyebrow">{!! $t('О платформе', 'About the platform', 'Despre platformă') !!}</p>
            <h2 class="i-h2" style="margin-top:10px;">
                <span data-lang="ru">Технологии в&nbsp;пользу <span class="i-grad-text">человека</span></span>
                <span data-lang="en">Technology for the <span class="i-grad-text">benefit of people</span></span>
                <span data-lang="ro">Tehnologie în beneficiul <span class="i-grad-text">oamenilor</span></span>
            </h2>
        </div>
        <div class="grid md:grid-cols-3 gap-6">
            <div class="i-card i-card--hover i-reveal" style="--d:.05s">
                <div class="i-card__icon">{!! $icon('bulb') !!}</div>
                <h3 class="i-h4" style="font-size:1.25rem;margin-bottom:10px;">{!! $t('Интеллектуальный матчинг', 'Intelligent matching', 'Matching inteligent') !!}</h3>
                {!! $p('Заполните профиль — ИИ найдёт тех, кто может стать вашим партнёром, клиентом или ментором.', 'Fill in your profile — AI will find those who can become your partner, client or mentor.', 'Completează profilul — AI va găsi cei care pot deveni partenerul, clientul sau mentorul tău.', 'text-sm text-gray-500 leading-relaxed') !!}
            </div>
            <div class="i-card i-card--hover i-reveal" style="--d:.15s">
                <div class="i-card__icon i-card__icon--yellow">{!! $icon('users') !!}</div>
                <h3 class="i-h4" style="font-size:1.25rem;margin-bottom:10px;">{!! $t('Сообщество лидеров', 'Community of leaders', 'Comunitate de lideri') !!}</h3>
                {!! $p('Выпускники Академии INSPIRE, социальные предприниматели, исследователи и активисты, меняющие Молдову.', 'INSPIRE Academy graduates, social entrepreneurs, researchers and activists changing Moldova.', 'Absolvenți ai Academiei INSPIRE, antreprenori sociali, cercetători și activiști care schimbă Moldova.', 'text-sm text-gray-500 leading-relaxed') !!}
            </div>
            <div class="i-card i-card--hover i-reveal" style="--d:.25s">
                <div class="i-card__icon i-card__icon--green">{!! $icon('bolt') !!}</div>
                <h3 class="i-h4" style="font-size:1.25rem;margin-bottom:10px;">{!! $t('От знакомства к результату', 'From meeting to outcome', 'De la cunoaștere la rezultat') !!}</h3>
                {!! $p('Ищете соучредителя для стартапа или эксперта для проекта? Платформа создаёт значимые связи.', 'Looking for a startup co-founder or a project expert? The platform creates meaningful connections.', 'Cauți co-fondator pentru startup sau expert pentru proiect? Platforma creează conexiuni semnificative.', 'text-sm text-gray-500 leading-relaxed') !!}
            </div>
        </div>
    </div>
</section>

{{-- ───────────── STEPS ───────────── --}}
<section class="i-section i-section--tint" id="join">
    <div class="i-container">
        <div class="text-center max-w-2xl mx-auto mb-14 i-reveal">
            <p class="i-eyebrow">{!! $t('Как присоединиться', 'How to join', 'Cum să te alături') !!}</p>
            <h2 class="i-h2" style="margin-top:10px;">{!! $t('Три простых шага', 'Three simple steps', 'Trei pași simpli') !!}</h2>
        </div>
        <div class="grid md:grid-cols-3 gap-6">
            @php
                $steps = [
                    ['01', ['Регистрация', 'Registration', 'Înregistrare'], ['Перейдите в Telegram-бот и заполните профиль: компетенции, интересы и цели.', 'Go to the Telegram bot and fill in your profile: competencies, interests and goals.', 'Mergi la bot-ul Telegram și completează profilul: competențe, interese și obiective.'], 'var(--blue-600)'],
                    ['02', ['ИИ-анализ', 'AI analysis', 'Analiză AI'], ['Алгоритм проанализирует профиль и найдёт участников с наибольшим потенциалом для партнёрства.', 'The algorithm will analyze your profile and find participants with the greatest partnership potential.', 'Algoritmul va analiza profilul și va găsi participanți cu cel mai mare potențial de parteneriat.'], 'var(--orange)'],
                    ['03', ['Сотрудничество', 'Collaboration', 'Colaborare'], ['Получите персонализированные рекомендации и начните диалог с вашим будущим партнёром.', 'Get personalized recommendations and start a dialogue with your future partner.', 'Obțineți recomandări personalizate și inițiați un dialog cu viitorul partener.'], 'var(--green)'],
                ];
            @endphp
            @foreach($steps as $i => $s)
                <div class="i-card i-card--spectrum i-reveal" style="--d:{{ .08 + $i * .12 }}s">
                    <div class="step-num" style="color:{{ $s[3] }};opacity:.9">{{ $s[0] }}</div>
                    <h3 class="i-h4" style="font-size:1.25rem;margin:14px 0 10px;">{!! $t(...$s[1]) !!}</h3>
                    {!! $p(...array_merge($s[2], ['text-sm text-gray-500 leading-relaxed'])) !!}
                </div>
            @endforeach
        </div>
        <div class="text-center mt-12 i-reveal">
            <a href="{{ $bot }}" target="_blank" rel="noopener" class="i-btn i-btn--lg">{!! $tg !!}<span>{!! $t('Начать сейчас', 'Start now', 'Începe acum') !!}</span></a>
        </div>
    </div>
</section>

{{-- ───────────── VALUES ───────────── --}}
<section class="i-section" id="values">
    <div class="i-container">
        <div class="text-center max-w-2xl mx-auto mb-14 i-reveal">
            <p class="i-eyebrow">{!! $t('Наши принципы', 'Our principles', 'Principiile noastre') !!}</p>
            <h2 class="i-h2" style="margin-top:10px;">{!! $t('Ценности сообщества', 'Community values', 'Valorile comunității') !!}</h2>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @php
                $values = [
                    ['group', '', ['Социальная сплочённость', 'Social cohesion', 'Coeziune socială'], ['Строим мосты между людьми, организациями и регионами Молдовы.', 'Building bridges between people, organizations and regions of Moldova.', 'Construim poduri între oameni, organizații și regiunile Moldovei.']],
                    ['shield', 'green', ['Устойчивое развитие', 'Sustainable development', 'Dezvoltare durabilă'], ['Каждое партнёрство — шаг к устойчивому будущему наших сообществ.', 'Every partnership is a step toward a sustainable future for our communities.', 'Fiecare parteneriat este un pas spre un viitor durabil al comunităților noastre.']],
                    ['bolt', 'yellow', ['Лидерство через действие', 'Leadership through action', 'Leadership prin acțiune'], ['Не просто нетворкинг — конкретные проекты и измеримые результаты.', 'Not just networking — concrete projects and measurable results.', 'Nu doar networking — proiecte concrete și rezultate măsurabile.']],
                    ['globe', 'purple', ['Глобальное гражданство', 'Global citizenship', 'Cetățenie globală'], ['Мыслим локально, действуем глобально — соединяем Молдову с международным опытом.', 'Think locally, act globally — connecting Moldova with international experience.', 'Gândim local, acționăm global — conectăm Moldova cu experiența internațională.']],
                    ['bulb', 'orange', ['Цифровая трансформация', 'Digital transformation', 'Transformare digitală'], ['Используем ИИ, чтобы делать сотрудничество эффективнее и доступнее.', 'We use AI to make collaboration more efficient and accessible.', 'Folosim AI pentru a face colaborarea mai eficientă și mai accesibilă.']],
                    ['badge', 'red', ['Ориентация на результат', 'Results-oriented', 'Orientare spre rezultat'], ['Каждая связь имеет цель. Мы создаём реальные проекты и добиваемся измеримых изменений в обществе.', 'Every connection has a purpose. We build real projects and achieve measurable changes in society.', 'Fiecare conexiune are un scop. Construim proiecte reale și realizăm schimbări măsurabile în societate.']],
                ];
            @endphp
            @foreach($values as $i => $v)
                <div class="i-card i-card--hover i-reveal" style="--d:{{ ($i % 3) * .1 }}s">
                    <div class="i-card__icon {{ $v[1] ? 'i-card__icon--'.$v[1] : '' }}">{!! $icon($v[0]) !!}</div>
                    <h3 class="i-h4" style="font-size:1.2rem;margin-bottom:10px;">{!! $t(...$v[2]) !!}</h3>
                    {!! $p(...array_merge($v[3], ['text-sm text-gray-500 leading-relaxed'])) !!}
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ───────────── INSPIRE NETWORK ───────────── --}}
<section class="i-section i-section--soft" id="network">
    <div class="i-container grid lg:grid-cols-2 gap-14 items-center">
        <div class="i-reveal">
            <p class="i-eyebrow">INSPIRE Network</p>
            <h2 class="i-h2" style="margin:10px 0 18px;">{!! $t('Движение, меняющее страну', 'A movement changing the country', 'O mișcare care schimbă țara') !!}</h2>
            <div class="i-spectrum-line" style="width:84px;margin-bottom:22px;"></div>
            {!! $p('INSPIRE Network (Межуниверситетская сеть за устойчивый мир, инновации, исследования и образование) — совместная инициатива университетов и НПО Молдовы при поддержке <strong class="text-ink">Посольства Королевства Нидерландов</strong>.', 'INSPIRE Network (Inter-university Network for Sustainable Peace, Innovation, Research and Education) — a joint initiative of Moldovan universities and NGOs supported by the <strong class="text-ink">Embassy of the Kingdom of the Netherlands</strong>.', 'INSPIRE Network (Rețeaua Interuniversitară pentru Pace Durabilă, Inovare, Cercetare și Educație) — o inițiativă comună a universităților și ONG-urilor din Moldova susținută de <strong class="text-ink">Ambasada Regatului Țărilor de Jos</strong>.', 'text-gray-500 leading-relaxed mb-4') !!}
            {!! $p('Академия лидерства INSPIRE готовит молодых лидеров для решения глобальных вызовов. INSPIRE Community — следующий шаг: платформа, где знания превращаются в партнёрство.', 'The INSPIRE Leadership Academy trains young leaders to tackle global challenges. INSPIRE Community is the next step: a platform where knowledge turns into partnership.', 'Academia de Leadership INSPIRE pregătește tineri lideri pentru provocările globale. INSPIRE Community este pasul următor: o platformă unde cunoașterea devine parteneriat.', 'text-gray-500 leading-relaxed mb-8') !!}
            <a href="{{ route('partners') }}" class="i-btn i-btn--secondary"><span>{!! $t('Партнёры', 'Partners', 'Parteneri') !!}</span>{!! $arrow !!}</a>
        </div>
        <div class="i-reveal" style="--d:.15s">
            <div class="i-card" style="padding:34px;display:grid;gap:22px;">
                <div class="flex items-center gap-5">
                    <div class="i-card__icon" style="width:64px;height:64px;margin:0;border-radius:20px;"><span class="i-h3 i-grad-text">100+</span></div>
                    <span class="font-semibold text-gray-700">{!! $t('выпускников программ INSPIRE', 'INSPIRE program graduates', 'absolvenți ai programelor INSPIRE') !!}</span>
                </div>
                <div class="flex items-center gap-5">
                    <div class="i-card__icon i-card__icon--yellow" style="width:64px;height:64px;margin:0;border-radius:20px;"><span class="i-h3" style="color:#b07d00">5+</span></div>
                    <span class="font-semibold text-gray-700">{!! $t('университетов-партнёров', 'partner universities', 'universități partenere') !!}</span>
                </div>
                <div class="flex items-center gap-5">
                    <div class="i-card__icon i-card__icon--green" style="width:64px;height:64px;margin:0;border-radius:20px;">
                        <svg class="nav-flag" style="width:36px;height:24px" viewBox="0 0 9 6" aria-hidden="true"><rect width="9" height="2" fill="#AE1C28"/><rect y="2" width="9" height="2" fill="#fff"/><rect y="4" width="9" height="2" fill="#21468B"/></svg>
                    </div>
                    <span class="font-semibold text-gray-700">{!! $t('поддержка Посольства Нидерландов', 'Supported by the Embassy of the Netherlands', 'Susținut de Ambasada Țărilor de Jos') !!}</span>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ───────────── SCHOOLS ───────────── --}}
<section class="i-section" id="schools">
    <div class="i-container">
        <div class="text-center max-w-2xl mx-auto mb-12 i-reveal">
            <p class="i-eyebrow">{!! $t('Образовательные программы', 'Educational programmes', 'Programe educaționale') !!}</p>
            <h2 class="i-h2" style="margin-top:10px;">{!! $t('Школы INSPIRE: 2023 — 2025', 'INSPIRE schools: 2023 — 2025', 'Școlile INSPIRE: 2023 — 2025') !!}</h2>
            {!! $p('Летняя школа мира 2023 и три школы Академии лидерства 2025: лидерство, технологии, медиа.', 'The 2023 Peace Summer School and the three schools of the 2025 Leadership Academy: leadership, technology, media.', 'Școala de vară a păcii 2023 și cele trei școli ale Academiei de Leadership 2025: leadership, tehnologii, media.', 'i-lead mx-auto mt-4') !!}
        </div>
        <div class="sch-line">
            @foreach($schools as $i => $sc)
                <a href="{{ $sc['url'] }}" class="i-card i-card--hover sch-card i-reveal" style="--d:{{ $i * .08 }}s">
                    <div class="sch-date"><span><i class="sch-dot" style="background:{{ $tones[$i % 4] }}"></i>{{ $sc['year'] }}</span><b>{!! $tm($sc['dates']) !!}</b></div>
                    <div class="sch-body">
                        <h3>{!! $tm($sc['title']) !!}</h3>
                        <div class="sch-meta"><span class="inline-flex items-center gap-1.5">{!! $icon('pin', 'w-4 h-4') !!}{!! $tm($sc['place']) !!}</span></div>
                        <div class="sch-topics">
                            @foreach($sc['topics'] as $tp)
                                <span class="i-chip">{!! $tm($tp) !!}</span>
                            @endforeach
                        </div>
                    </div>
                    <span class="i-btn i-btn--secondary i-btn--sm" aria-hidden="true">{!! $t('Подробнее', 'Details', 'Detalii') !!} {!! $arrow !!}</span>
                </a>
            @endforeach
        </div>
        <div class="flex flex-wrap justify-center gap-4 mt-10 i-reveal">
            <a href="{{ route('academy2025') }}" class="i-btn"><span>{!! $t('Академия 2025', 'Academy 2025', 'Academia 2025') !!}</span>{!! $arrow !!}</a>
            <a href="{{ route('academy2023') }}" class="i-btn i-btn--secondary"><span>{!! $t('Академия 2023', 'Academy 2023', 'Academia 2023') !!}</span>{!! $arrow !!}</a>
        </div>
    </div>
</section>

{{-- ───────────── EXPERTS ───────────── --}}
<section class="i-section i-section--soft">
    <div class="i-container">
        <div class="flex flex-wrap items-end justify-between gap-4 mb-9">
            <div class="i-reveal"><h2 class="i-h2">{!! $t('Эксперты и наставники', 'Experts and mentors', 'Experți și mentori') !!}</h2><div class="i-spectrum-line" style="width:84px;margin-top:14px;"></div></div>
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

{{-- ───────────── PHOTO ───────────── --}}
<section class="i-section">
    <div class="i-container">
        <div class="flex flex-wrap items-end justify-between gap-4 mb-9">
            <div class="i-reveal"><h2 class="i-h2">{!! $t('Фото', 'Photos', 'Fotografii') !!}</h2><div class="i-spectrum-line" style="width:84px;margin-top:14px;"></div></div>
            <a href="{{ route('academy2023') }}#photo" class="i-btn i-btn--secondary i-reveal"><span>{!! $t('Все фото', 'All photos', 'Toate fotografiile') !!} ({{ count($photos['y2023']) }})</span>{!! $arrow !!}</a>
        </div>
        <div class="gallery gallery--strip i-reveal">
            @foreach(array_slice($photos['y2023'], 0, 24) as $ph)
                <a class="gal-item" href="{{ route('academy2023') }}#photo" tabindex="-1"><img src="{{ $img($ph) }}" alt="" loading="lazy" width="150" height="150"></a>
            @endforeach
        </div>
    </div>
</section>

{{-- ───────────── VIDEO ───────────── --}}
<section class="i-section">
    <div class="i-container">
        <div class="mb-9 i-reveal"><h2 class="i-h2">{!! $t('Видео', 'Video', 'Video') !!}</h2><div class="i-spectrum-line" style="width:84px;margin-top:14px;"></div></div>
        @include('public._videos', ['videos' => $videos])
    </div>
</section>

{{-- ───────────── PARTNERS ───────────── --}}
<section class="i-section i-section--soft">
    <div class="i-container">
        <div class="flex flex-wrap items-end justify-between gap-4 mb-9">
            <div class="i-reveal"><h2 class="i-h2">{!! $t('Донор и организаторы', 'Donor and organisers', 'Donator și organizatori') !!}</h2><div class="i-spectrum-line" style="width:84px;margin-top:14px;"></div></div>
            <a href="{{ route('partners') }}" class="i-btn i-btn--secondary i-reveal"><span>{!! $t('Все партнёры', 'All partners', 'Toți partenerii') !!} ({{ count($partners) }})</span>{!! $arrow !!}</a>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            @foreach($orgs as $j => $pt)
                <a class="logo-tile i-reveal" style="--d:{{ ($j % 6) * .05 }}s" href="{{ $pt['url'] ?: route('partners') }}" @if($pt['url']) target="_blank" rel="noopener" @endif title="{{ $pt['name'] }}"><img src="{{ $img($pt['img']) }}" alt="{{ $pt['name'] }}" loading="lazy"></a>
            @endforeach
        </div>
    </div>
</section>

{{-- ───────────── FAQ ───────────── --}}
<section class="i-section i-section--soft" id="faq">
    <div class="i-container i-container--narrow">
        <div class="text-center mb-12 i-reveal">
            <p class="i-eyebrow">FAQ</p>
            <h2 class="i-h2" style="margin-top:10px;">{!! $t('Частые вопросы', 'Frequently asked questions', 'Întrebări frecvente') !!}</h2>
        </div>
        @php
            $faq = [
                [['Кто может присоединиться?', 'Who can join?', 'Cine se poate alătura?'], ['Платформа открыта для выпускников программ INSPIRE, студентов университетов-партнёров, социальных предпринимателей и активистов, разделяющих наши ценности.', 'The platform is open to INSPIRE program graduates, students of partner universities, social entrepreneurs and activists who share our values.', 'Platforma este deschisă absolvenților programelor INSPIRE, studenților universităților partenere, antreprenorilor sociali și activiștilor care împărtășesc valorile noastre.']],
                [['Как ИИ подбирает партнёров?', 'How does AI match partners?', 'Cum selectează AI partenerii?'], ['Алгоритм анализирует навыки, интересы, цели и опыт, сопоставляя их с профилями других участников для нахождения наиболее перспективных связей.', "The algorithm analyzes skills, interests, goals and experience, matching them with other participants' profiles to find the most promising connections.", 'Algoritmul analizează abilitățile, interesele, obiectivele și experiența, comparându-le cu profilurile altor participanți pentru a găsi conexiunile cele mai promițătoare.']],
                [['Платформа бесплатна?', 'Is the platform free?', 'Platforma este gratuită?'], ['Да, INSPIRE Community — некоммерческая инициатива для развития лидерского потенциала Молдовы.', 'Yes, INSPIRE Community is a non-profit initiative to develop the leadership potential of Moldova.', 'Da, INSPIRE Community este o inițiativă non-profit pentru a dezvolta potențialul de leadership al Moldovei.']],
                [['Нужно ли быть выпускником INSPIRE Academy?', 'Do I need to be an INSPIRE Academy graduate?', 'Trebuie să fiu absolvent al INSPIRE Academy?'], ['Приоритет — у выпускников программ INSPIRE, но платформа открыта для всех, готовых вносить вклад в устойчивое развитие и социальные изменения.', 'Priority is given to INSPIRE program graduates, but the platform is open to anyone willing to contribute to sustainable development and social change.', 'Prioritatea o au absolvenții programelor INSPIRE, dar platforma este deschisă tuturor celor dispuși să contribuie la dezvoltarea durabilă și schimbarea socială.']],
            ];
        @endphp
        <div class="grid gap-3">
            @foreach($faq as $i => $f)
                <details class="i-card faq i-reveal" style="--d:{{ $i * .07 }}s">
                    <summary><span>{!! $t(...$f[0]) !!}</span><span class="plus" aria-hidden="true"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M12 5v14M5 12h14"/></svg></span></summary>
                    <div class="faq-body">{!! $p(...$f[1]) !!}</div>
                </details>
            @endforeach
        </div>
    </div>
</section>

{{-- ───────────── CTA ───────────── --}}
<section class="i-section">
    <div class="i-container">
        <div class="i-airy i-reveal" style="border-radius:var(--r-2xl);border:1px solid var(--line);padding:68px 28px;text-align:center;box-shadow:var(--sh-md);">
            <img src="{{ asset('brand/logo-mark.png') }}" alt="" width="120" height="120" class="i-float" style="width:112px;height:auto;margin:0 auto 14px;">
            <h2 class="i-h2" style="max-width:640px;margin:0 auto 14px;">{!! $t('Готовы стать частью сообщества?', 'Ready to join the community?', 'Ești gata să te alături comunității?') !!}</h2>
            {!! $p('Начните свой путь прямо сейчас — зарегистрируйтесь через Telegram-бот за одну минуту.', 'Start your journey right now — register via Telegram bot in one minute.', 'Începe-ți călătoria chiar acum — înregistrează-te prin bot-ul Telegram în un minut.', 'i-lead mx-auto mb-9') !!}
            <a href="{{ $bot }}" target="_blank" rel="noopener" class="i-btn i-btn--lg">{!! $tg !!}<span>{!! $t('Подать заявку', 'Apply now', 'Depune cererea') !!}</span></a>
        </div>
    </div>
</section>
@endsection
