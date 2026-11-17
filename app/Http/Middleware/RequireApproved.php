<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * В кабинет заходит любой зарегистрированный участник, но игра, список участников и возможности
 * открыты только одобренным модератором. Остальным показывается, на каком они этапе.
 */
class RequireApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = view()->shared('accountUser');

        if ($user && $user->isApproved()) {
            return $next($request);
        }

        $rejected = (bool) $user?->isRejected();
        $message = $rejected
            ? \App\Support\Lang::t('Модератор отказал вам в доступе к игре.', 'The moderator has denied you access to the game.', 'Moderatorul ți-a refuzat accesul la joc.')
            : \App\Support\Lang::t('Модератор ещё не посмотрел ваш профиль.', 'The moderator has not reviewed your profile yet.', 'Moderatorul nu ți-a verificat încă profilul.');

        if ($request->expectsJson()) {
            return response()->json(['message' => $message, 'moderation' => $rejected ? 'rejected' : 'pending'], 403);
        }

        return response()->view('account.moderation', [
            'rejected' => $rejected,
            'note'     => $user?->moderation_note,
            'section'  => match (true) {
                $request->routeIs('account.game*')          => \App\Support\Lang::t('Игра', 'Game', 'Joc'),
                $request->routeIs('account.opportunities*') => \App\Support\Lang::t('Возможности', 'Opportunities', 'Oportunități'),
                default                                     => \App\Support\Lang::t('Люди', 'People', 'Oameni'),
            },
        ]);
    }
}
