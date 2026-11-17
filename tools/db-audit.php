<?php

/**
 * Сколько запросов к базе делает каждая страница и сколько миллисекунд уходит на ответ.
 *
 *     php tools/db-audit.php            # публичный сайт и кабинет
 *     php tools/db-audit.php --queries  # показать сами запросы
 *
 * Страницы вызываются внутри приложения (без веб-сервера), с настоящими сессиями из .env, поэтому видно и запросы сессии и кэша.
 * Для кабинета берётся первый одобренный участник и создаётся ему одноразовая ссылка входа (в базе остаётся одна запись).
 */

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Cookie;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->instance('request', Request::create('/'));
$app->make(Kernel::class)->bootstrap();

$showQueries = in_array('--queries', $argv, true);

$cookies = [];

function hit(string $uri, array &$cookies, bool $showQueries): array
{
    global $app;

    $queries = [];
    DB::flushQueryLog();
    DB::enableQueryLog();

    $kernel = $app->make(Kernel::class);
    $request = Request::create($uri, 'GET', [], $cookies, [], ['HTTP_ACCEPT' => 'text/html', 'HTTP_X_FORWARDED_PROTO' => 'http']);

    $start = hrtime(true);
    $response = $kernel->handle($request);
    $ms = (hrtime(true) - $start) / 1e6;

    foreach ($response->headers->getCookies() as $cookie) {
        /** @var Cookie $cookie */
        $cookies[$cookie->getName()] = $cookie->getValue();
    }

    $log = DB::getQueryLog();
    $dbMs = array_sum(array_column($log, 'time'));
    $kernel->terminate($request, $response);

    return ['status' => $response->getStatusCode(), 'ms' => $ms, 'q' => count($log), 'dbms' => $dbMs, 'log' => $log, 'location' => $response->headers->get('Location')];
}

function row(string $label, array $r, bool $showQueries): void
{
    printf("%-34s %3d  %4d запр.  БД %6.1f мс  всего %7.1f мс\n", $label, $r['status'], $r['q'], $r['dbms'], $r['ms']);
    if ($showQueries) {
        foreach ($r['log'] as $q) {
            echo '      ', preg_replace('/\s+/', ' ', mb_substr($q['query'], 0, 150)), "\n";
        }
    }
}

echo "── публичный сайт (первый заход, потом повторный) ──\n";
foreach (['/', '/experts', '/photo', '/publications', '/opportunities', '/events/schools-2025', '/partners'] as $uri) {
    $c = [];
    row($uri.' (холодный)', hit($uri, $c, $showQueries), $showQueries);
    row($uri.' (повтор)', hit($uri, $c, $showQueries), $showQueries);
}

echo "\n── кабинет ──\n";
$user = \App\Models\BotUser::where('status', 'approved')->orderBy('id')->first();
if (! $user) {
    echo "нет одобренных участников для проверки кабинета\n";
    exit;
}
$token = \App\Models\LoginToken::generateFor((int) $user->telegram_id);
$cabinet = [];
$r = hit('/app/account/auth?token='.$token->token, $cabinet, $showQueries);
row('вход по токену', $r, $showQueries);
foreach (['/app/account', '/app/account/profile', '/app/account/profile/edit', '/app/account/people', '/app/account/opportunities', '/app/account/game', '/app/account/game/api/state', '/app/account/game/api/quest'] as $uri) {
    row($uri, hit($uri, $cabinet, $showQueries), $showQueries);
}
