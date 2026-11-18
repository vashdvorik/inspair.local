<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\BotUser;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Аватарка участника берётся из Telegram и хранится у нас файлом avatars/<telegram_id>.jpg.
 * Скачивается при регистрации в боте, а потом при необходимости обновляется сама: если файла нет
 * (например, при переносе сайта) или он старше нескольких дней, кабинет запрашивает фото у Telegram заново.
 */
class TelegramAvatar
{
    public const MAX_AGE_DAYS = 3;

    public static function path(BotUser $user): string
    {
        return "avatars/{$user->telegram_id}.jpg";
    }

    public static function isFresh(BotUser $user): bool
    {
        $disk = Storage::disk('public');
        $path = self::path($user);

        return $disk->exists($path) && $disk->lastModified($path) > now()->subDays(self::MAX_AGE_DAYS)->getTimestamp();
    }

    /** Скачивает актуальное фото профиля Telegram. Возвращает true, если файл сохранён. Не бросает исключений. */
    public function fetch(BotUser $user): bool
    {
        $token = (string) config('nutgram.token');
        if ($token === '' || ! $user->telegram_id) {
            return false;
        }

        try {
            $api = "https://api.telegram.org/bot{$token}";
            $photos = Http::timeout(6)->get("{$api}/getUserProfilePhotos", ['user_id' => $user->telegram_id, 'limit' => 1])->json('result.photos.0');
            if (empty($photos)) {
                return false;
            }

            // Последний размер в наборе самый крупный
            $fileId = end($photos)['file_id'] ?? null;
            $filePath = $fileId ? Http::timeout(6)->get("{$api}/getFile", ['file_id' => $fileId])->json('result.file_path') : null;
            if (! $filePath) {
                return false;
            }

            $file = Http::timeout(10)->get("https://api.telegram.org/file/bot{$token}/{$filePath}");
            if (! $file->successful() || $file->body() === '') {
                return false;
            }

            $path = self::path($user);
            Storage::disk('public')->put($path, $file->body());
            if ($user->avatar_path !== $path) {
                $user->update(['avatar_path' => $path]);
            }

            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
