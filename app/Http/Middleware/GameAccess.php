<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\GameService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Закрывает разделы кабинета лестницей уровней: ->middleware('game.access:people').
 */
class GameAccess
{
    public function __construct(private readonly GameService $game) {}

    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $user = view()->shared('accountUser');

        if ($user && ! $this->game->canAccess($user, $feature)) {
            return redirect()->route('account.game', ['locked' => $feature]);
        }

        return $next($request);
    }
}
