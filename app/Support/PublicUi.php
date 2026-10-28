<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Helpers for the public pages: three-language text (ru / en / ro, switched with html[lang]),
 * JSON content files from resources/data, and a few shared snippets (icons, Telegram button).
 */
final class PublicUi
{
    public const LANGS = ['ru', 'en', 'ro'];
    public const BOT = 'https://t.me/Inspiremoldova_bot';

    /** @var array<string, array> */
    private static array $cache = [];

    /** Load resources/data/<name>.json (cached for the request). */
    public static function content(string $name): array
    {
        return self::$cache[$name] ??= json_decode((string) file_get_contents(resource_path("data/{$name}.json")), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * Asset URL with a version (?v=<file modification time>): after a deploy the browser fetches the new
     * CSS/JS/image by itself, nobody has to press F5. Only files that really changed get a new version.
     */
    public static function versioned(string $path): string
    {
        $path = ltrim($path, '/');
        $file = public_path($path);

        return asset($path) . (is_file($file) ? '?v=' . filemtime($file) : '');
    }
    /** Plain text is escaped, text that already contains markup is trusted (developer-authored content). */
    private static function esc(?string $s): string
    {
        $s ??= '';

        return str_contains($s, '<') ? $s : e($s);
    }

    /** Inline text in all languages: ['ru' => ..., 'en' => ..., 'ro' => ...] */
    public static function span(array $v): string
    {
        $out = '';
        foreach (self::LANGS as $l) {
            $out .= '<span data-lang="'.$l.'">'.self::esc($v[$l] ?? ($v['en'] ?? '')).'</span>';
        }

        return $out;
    }

    /** Paragraphs in all languages. */
    public static function paras(array $v, string $class = ''): string
    {
        $out = '';
        foreach (self::LANGS as $l) {
            $out .= '<p class="'.e($class).'" data-lang="'.$l.'">'.self::esc($v[$l] ?? ($v['en'] ?? '')).'</p>';
        }

        return $out;
    }

    /** Same, but the data is keyed language-first and addressed with a dot path: $T[lang][path]. */
    public static function path(array $T, string $path, bool $paragraph = false, string $class = ''): string
    {
        $map = [];
        foreach (self::LANGS as $l) {
            $map[$l] = data_get($T[$l] ?? [], $path);
        }

        return $paragraph ? self::paras($map, $class) : self::span($map);
    }

    /** Initials for people without a photo ("Prof. Dr. Anna Moldovan" → "AM"). */
    public static function initials(string $name): string
    {
        $name = preg_replace('/^(Mr\.?|Ms\.?|Prof\.?|Dr\.?|Commander)\s*/u', '', trim($name));
        $name = preg_replace('/^(Prof\.?|Dr\.?)\s*/u', '', (string) $name);
        $parts = preg_split('/\s+/u', (string) $name) ?: [];

        return mb_strtoupper(mb_substr($parts[0] ?? '?', 0, 1).mb_substr($parts[1] ?? '', 0, 1));
    }

    public static function telegramIcon(): string
    {
        return '<svg class="w-[18px] h-[18px]" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>';
    }

    public static function arrow(): string
    {
        return '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>';
    }

    /** Outline icons (heroicons paths). */
    public static function icon(string $key, string $class = 'w-6 h-6'): string
    {
        static $paths = [
            'bulb'   => 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z',
            'users'  => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
            'bolt'   => 'M13 10V3L4 14h7v7l9-11h-7z',
            'chat'    => 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z',
            'sliders' => 'M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4',
            'case'    => 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
            'photo'   => 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z',
            'doc'     => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
            'code'    => 'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4',
            'group'  => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
            'shield' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
            'globe'  => 'M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9',
            'badge'  => 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z',
            'heart'  => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z',
            'leaf'   => 'M5 21c0-9 5-14 14-14 0 9-5 14-14 14zm0 0c0-4 2-7 6-9',
            'cal'    => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
            'pin'    => 'M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z',
            'book'   => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
            'award'  => 'M9 12l2 2 4-4M12 3l2.4 2.1 3.2-.2.9 3 2.5 1.9-1 3 1 3-2.5 1.9-.9 3-3.2-.2L12 21l-2.4-2.1-3.2.2-.9-3-2.5-1.9 1-3-1-3 2.5-1.9.9-3 3.2.2z',
        ];

        return '<svg class="'.$class.'" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="'.($paths[$key] ?? $paths['bolt']).'"/></svg>';
    }
}
