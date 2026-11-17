<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\Lang;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

/** Включает язык кабинета (ru / en / ro) для текущего запроса: даты и тексты Laravel тоже переключаются. */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $lang = Lang::current();
        app()->setLocale($lang);
        Carbon::setLocale($lang);

        return $next($request);
    }
}
