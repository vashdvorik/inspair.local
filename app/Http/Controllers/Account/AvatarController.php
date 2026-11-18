<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\BotUser;
use App\Services\TelegramAvatar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Отдаёт аватарку участника. Не зависит от ссылки public/storage: файл читается с диска напрямую.
 * Если файла нет или он устарел, фото тихо подтягивается из Telegram (не чаще раза в 10 минут на участника).
 */
class AvatarController extends Controller
{
    public function show(Request $request, BotUser $botUser, TelegramAvatar $telegram): BinaryFileResponse
    {
        $viewer = $request->attributes->get('accountUser') ?? view()->shared('accountUser');
        abort_unless($viewer && ($viewer->id === $botUser->id || $botUser->isApproved()), 404);

        if (! TelegramAvatar::isFresh($botUser) && Cache::add("avatar-try:{$botUser->id}", 1, 600)) {
            $telegram->fetch($botUser);
        }

        $disk = Storage::disk('public');
        $path = TelegramAvatar::path($botUser);
        abort_unless($disk->exists($path), 404);

        return response()->file($disk->path($path), [
            'Content-Type'  => 'image/jpeg',
            'Cache-Control' => 'private, max-age=86400',
        ]);
    }
}
