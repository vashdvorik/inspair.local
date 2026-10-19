<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\GameService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Когда игра выключена в админке, её страницы и API не существуют (404).
 */
class GameEnabled
{
    public function __construct(private readonly GameService $game) {}

    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($this->game->isEnabled(), 404);

        return $next($request);
    }
}
