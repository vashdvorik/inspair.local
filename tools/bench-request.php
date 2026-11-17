<?php

/** Один холодный запрос в новом процессе: php tools/bench-request.php / — печатает миллисекунды (как на сервере, где приложение каждый раз стартует заново). */

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

$_SERVER['REQUEST_URI'] = getenv('BENCH_HEAVY') ? '/admin' : ($argv[1] ?? '/'); // как при настоящем запросе; BENCH_HEAVY=1 включает полный набор пакетов для сравнения
$t0 = hrtime(true);
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$request = Request::create($argv[1] ?? '/', 'GET', [], [], [], ['HTTP_ACCEPT' => 'text/html']);
$response = $kernel->handle($request);
printf("%d %.0f\n", $response->getStatusCode(), (hrtime(true) - $t0) / 1e6);
