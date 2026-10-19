<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\BotUser;
use App\Services\GameService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireAccountAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $telegramId = session('account_telegram_id');

        if (! $telegramId) {
            return redirect()->route('account.login');
        }

        // Enforce server-side session expiry (independent of cookie lifetime)
        $expires = session('_account_expires');
        if (! $expires || time() > (int) $expires) {
            session()->forget('account_telegram_id');
            session()->forget('_account_expires');

            return redirect()->route('account.login')
                ->with('error', 'Сессия истекла. Войдите снова.');
        }

        $user = BotUser::where('telegram_id', $telegramId)
            ->where('status', BotUser::STATUS_APPROVED)
            ->first();

        if (! $user) {
            session()->forget('account_telegram_id');
            session()->forget('_account_expires');

            return redirect()->route('account.login')
                ->with('error', 'Доступ закрыт. Убедитесь, что ваша заявка одобрена.');
        }

        // Share the authenticated user with all views in this middleware group
        view()->share('accountUser', $user);

        // Игра: помощники для шаблонов (что виден в чужом профиле) и отметка активности
        $game = app(GameService::class);
        view()->share('game', $game);
        view()->share('see', fn (BotUser $target, string $field): bool => $game->canSee($user, $target, $field));


        return $next($request);
    }
}
