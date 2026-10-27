# INSPIRE EDU Network — Design System v1.0

Live guide: **`/design/`** (open `public/design/index.html`). Everything on the site is built from it.

Source of truth is the logo (`user/photo_2026-10-14_15-20-16.jpg`): a navy wordmark, a sky-blue globe made of
coloured nodes, and a dove with a spectrum wing. Mood: **light, fresh, connected** — one bright theme, no dark mode.

## Files

| File | Purpose |
|---|---|
| `public/design/inspire.css` | Tokens (CSS variables), Montserrat `@font-face`, all `i-*` components, motion |
| `public/design/inspire.js` | Scroll reveal, nav shadow, pausing off-screen animation, low-power mode (no dependencies) |
| `public/design/tailwind.css` (built from `tailwind.config.cjs`) | Compiled Tailwind utilities that map the tokens (`bg-brand-600`, `text-navy-800`, `rounded-i-xl` …) |
| `public/design/pages.css` | Layout pieces of the public pages (hero art, people cards, logo tiles, programme text, timeline) |
| `public/design/fonts.css`, `public/design/fonts/` | Self-hosted Montserrat (Latin + Cyrillic), used by the Filament admin |
| `public/brand/*` | Logo files (see below) |
| `public/site.webmanifest` | App name + icons |

### Logo files (`public/brand/`)
`logo.png` primary (transparent, 960 px) · `logo-small.png` · `logo-full-large.png` · `logo-mark.png` globe + dove only ·
`icon-192.png` / `icon-512.png` app icons · `apple-touch-icon.png` · `favicon.ico` / `favicon-32.png` · `og-image.png` 1200×630 social card.

Rules: only on white or very light tints; keep free space equal to the height of the “I”; min height 40 px (primary) / 24 px (mark);
never stretch, recolour, add effects or place on dark/saturated colour.

## Colour (sampled from the logo)

| Token | Hex | Use |
|---|---|---|
| `--navy-800` | `#00387A` | wordmark, strongest text accents |
| `--blue-600` | `#006DE0` | **primary** — buttons, links, focus |
| `--blue-500` / `--sky` | `#0682E4` / `#019EFF` | gradients, highlights |
| `--blue-100/50`, `--bg-tint`, `--bg-soft` | `#DCEDFE`, `#F0F7FF`, `#EAF3FE`, `#F6FAFF` | soft fills, sections |
| `--yellow` `--orange` `--red` `--green` `--purple` | `#FDBA07` `#FF6C0B` `#E31E27` `#3D932E` `#72299C` | spectrum accents (+ `-100` tints) |
| `--ink` `--text` `--muted` `--subtle` | `#0E2442` `#26405F` `#5B6F8D` `#8497B1` | headings / body / secondary / hints |
| `--line` `--line-strong` | `#DCE8F5` `#C3D5EA` | borders |

Balance: ~80 % white and tints, ~15 % blue, ~5 % spectrum. One accent colour per component. The darkest colour allowed is Navy 800
(text and logo only).

## Type
One family — **Montserrat** (800 headlines, 400/500 text, spaced capitals for labels). Scale: `--fs-xs … --fs-6xl`
(fluid `clamp()` for 3xl+). Body 16 px / 1.65, headings 1.1–1.25, tracking −0.025 em on large headings.

## Space, shape, elevation
4 px grid (`--s-1 … --s-24`). Radii: 8 / 12 / 18 / **26 (cards)** / 34 / pill (buttons). Shadows are always blue-tinted
(`--sh-xs … --sh-lg`, `--sh-primary`). Section padding ≥ 80 px.

## Components (`i-` prefix)
`i-btn` (+ `--secondary --ghost --accent --navy --danger --sm --lg --block`) · `i-card` (+ `--hover --tint --flat --spectrum`,
`i-card__icon--yellow|orange|green|purple|red`) · `i-chip` · `i-badge` (+ colours) · `i-avatar` · `i-input / i-select / i-textarea / i-label / i-hint` ·
`i-alert` (+ `--success --warning --danger`) · `i-table` · `i-nav`, `i-lang` · `i-airy` (hero background) · `i-nodes` (network decoration) ·
`i-spectrum-line` · typography helpers `i-h1…i-h4`, `i-lead`, `i-eyebrow`, `i-grad-text`.

## Motion
Only `transform` and `opacity` are animated (cheap for the CPU). Reveal on scroll `.i-reveal` (`style="--d:.1s"` for stagger), hero entrance
`.i-in .i-in-1…5`, float `.i-float`, drifting pastel blobs and nodes in `.i-airy` / `.i-nodes`, button sheen, card lift on hover.
Everything stops for `prefers-reduced-motion`; decoration is dropped on low-power devices (`.i-lite`, set by `inspire.js`) and paused
when off-screen.

## Using it on a new page
```html
<link rel="stylesheet" href="/design/inspire.css">
<script src="https://cdn.tailwindcss.com"></script><script src="/design/tailwind.js"></script>   <!-- optional, for utilities -->
<body class="i-body"> … <script src="/design/inspire.js"></script>
```
Blade pages in this repo: `landing`, `account/layout`, `account/login` already do this. The Filament admin takes the same palette from
`AdminPanelProvider` (colours, logo, `darkMode(false)`) plus `resources/views/filament/motion.blade.php` (light skin).
The academy pages are generated from the scratch generator and use the same tokens; the Russian academy pages keep the original archived design.

## Checklist for every new screen
1. White or tinted background — no dark sections, no black. 2. Logo from `/brand`. 3. One primary button per view. 4. Cards `i-card`, radius 26.
5. Spectrum colours only as small accents. 6. Text ≥ 16 px, contrast ≥ 4.5:1. 7. Motion = reveal + hover only; test with reduced motion.

## Public site structure

Original language is English (default); RU and RO are translations. Menu follows the customer brief (`user/` folder, not published):

| Menu | Route | View |
|---|---|---|
| Home | `/` | `public/home.blade.php` (hero on a photo, ticker, INSPIRE cards, story, values, core areas, events, experts, who can join, key features, alumni, photo, video, partners) |
| Events → Hackathon 2026 | `/events/hackathon-2026` | `public/hackathon.blade.php` (stub: information coming soon) |
| Events → Schools 2025 | `/events/schools-2025`, `/events/schools-2025/school-{1..3}` | `public/academy2025/*` (Final Conference + three schools) |
| Events → School 2023 | `/events/school-2023` | `public/academy2023/index.blade.php` |
| Experts | `/experts` | `public/experts.blade.php` |
| Photo | `/photo` | `public/photo.blade.php` (six albums, lightbox `design/gallery.js`) |
| Video | `/video` | `public/video.blade.php` (existing YouTube videos) |
| Partners | `/partners` | `public/partners.blade.php` |
| Publications | `/publications` | `public/publications.blade.php` (stub) |

Old addresses `/academy-2025…` and `/academy-2023` redirect with 301. Registration buttons and deadlines of finished events are not shown.

Shared layout: `public/layout.blade.php` (menu with Events dropdown, language switch en/ru/ro, footer). Text is written in three languages inline (`data-lang`) via helpers in `App\Support\PublicUi`. Content lives in `resources/data/*.json`: `site.json` (all texts from the brief: hero, acronym cards, story, values, who/features/alumni, events, albums), `gallery.json` (photo manifest), `people`, `partners`, `academy2025`, `academy2023`, `videos`.

Photos: customer originals in `user/` (git-ignored) are compressed to WebP (full 1400px + 520px thumbnail) in `public/img/gallery/<album>/NNN.webp` and `NNN-t.webp`; hero photo in `public/img/hero/`.

## Performance notes

- Tailwind is compiled, not run in the browser: after adding utility classes in Blade run `npm run build:css` (config `tailwind.config.cjs` → `public/design/tailwind.css`, committed; the server needs no Node).
- Decorative motion: off on phones/touch, paused after 20 s idle or in a hidden tab (`public/design/inspire.js`, `.i-idle`); no `filter: blur`/`backdrop-filter`; long pages use `content-visibility: auto`.
- The hero photo has its soft edges baked into WebP with alpha (`public/img/hero/group-soft*.webp`), no CSS mask. Telegram's script is loaded only inside Telegram.
