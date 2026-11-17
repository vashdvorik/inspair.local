<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

/**
 * Страницы сайта и кабинета всегда проверяются заново, а у тех, кто заходил до версионности,
 * один раз сбрасывается кэш браузера. Файл .htaccess для этого не нужен.
 *
 * Как это работает:
 *  - HTML отдаётся с Cache-Control: no-cache, поэтому браузер, прокси и CDN не показывают старую страницу;
 *  - ссылки на стили, скрипты и картинки внутри страницы несут ?v=<время файла>-<CACHE_EPOCH> (PublicUi::versioned);
 *  - если у посетителя нет куки inspire_cache с текущим CACHE_EPOCH, ответ несёт Clear-Site-Data: "cache"
 *    (браузер выбрасывает всё, что накопил этот сайт) и ставит куку, чтобы очистка не повторялась.
 *    Куки, сессия и localStorage не трогаются: выбранный язык и вход в кабинет сохраняются.
 *
 * Чтобы заставить всех обновиться ещё раз, увеличьте CACHE_EPOCH в .env и выполните php artisan config:clear.
 */
class FreshPages
{
    public const COOKIE = 'inspire_cache';

    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        if (! $request->isMethod('GET') || $response->getStatusCode() >= 300 || ! $this->isHtml($response)) {
            return $response;
        }

        $response->headers->set('Cache-Control', 'no-cache, private, max-age=0, must-revalidate');

        // Clear-Site-Data работает только по HTTPS
        $epoch = (string) config('app.cache_epoch');
        if ($request->isSecure() && (string) $request->cookies->get(self::COOKIE) !== $epoch) {
            $response->headers->set('Clear-Site-Data', '"cache"');
            $response->headers->setCookie(new Cookie(self::COOKIE, $epoch, now()->addYear(), '/', null, true, true, false, Cookie::SAMESITE_LAX));
        }

        return $response;
    }

    private function isHtml(Response $response): bool
    {
        return str_contains((string) $response->headers->get('Content-Type'), 'text/html');
    }
}
