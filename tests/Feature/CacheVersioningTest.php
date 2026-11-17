<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Middleware\FreshPages;
use Tests\TestCase;

/** Полная версионность: адреса файлов меняются с файлом, страницы не кэшируются, старый кэш сбрасывается один раз. */
class CacheVersioningTest extends TestCase
{
    public function test_every_asset_link_carries_a_version_with_the_epoch(): void
    {
        $epoch = config('app.cache_epoch');
        $html = $this->get('/')->assertOk()->getContent();

        foreach (['design/inspire.css', 'design/pages.css', 'design/tailwind.css', 'design/inspire.js', 'img/hero/og.jpg'] as $asset) {
            $this->assertMatchesRegularExpression('#' . preg_quote($asset, '#') . '\?v=\d+-' . $epoch . '#', $html, $asset);
        }
    }

    public function test_images_and_fonts_inside_stylesheets_are_versioned(): void
    {
        foreach (['inspire.css', 'pages.css', 'fonts.css'] as $css) {
            $content = (string) file_get_contents(public_path("design/{$css}"));
            preg_match_all('#url\(([^)]+)\)#', $content, $m);
            foreach ($m[1] as $url) {
                if (str_starts_with($url, 'data:')) {
                    continue;
                }
                $this->assertStringContainsString('?v=', $url, "{$css}: {$url}");
            }
        }
    }

    public function test_pages_are_never_served_from_a_cache(): void
    {
        $cache = $this->get('/')->assertOk()->headers->get('Cache-Control');
        $this->assertStringContainsString('no-cache', $cache);
        $this->assertStringContainsString('must-revalidate', $cache);
    }

    public function test_old_visitors_get_one_cache_purge_over_https(): void
    {
        $first = $this->get('https://localhost/')->assertOk();
        $first->assertHeader('Clear-Site-Data', '"cache"');
        $first->assertCookie(FreshPages::COOKIE, (string) config('app.cache_epoch'), false);

        // с актуальной кукой повторной очистки нет
        $this->withUnencryptedCookie(FreshPages::COOKIE, (string) config('app.cache_epoch'))
            ->get('https://localhost/')->assertOk()->assertHeaderMissing('Clear-Site-Data');

        // после увеличения CACHE_EPOCH очистка срабатывает снова
        config(['app.cache_epoch' => config('app.cache_epoch') + 1]);
        $this->withUnencryptedCookie(FreshPages::COOKIE, (string) (config('app.cache_epoch') - 1))
            ->get('https://localhost/')->assertHeader('Clear-Site-Data', '"cache"');
    }

    public function test_no_purge_for_plain_http_and_non_html(): void
    {
        $this->get('http://localhost/')->assertOk()->assertHeaderMissing('Clear-Site-Data');
        $this->get('https://localhost/up')->assertHeaderMissing('Clear-Site-Data'); // не страница сайта
    }
}
