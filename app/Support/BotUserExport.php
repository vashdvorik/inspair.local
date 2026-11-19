<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\BotUser;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Выгрузка всех участников файлом (CSV для Excel и Google Sheets) для администраторов.
 * Кодировка UTF-8 с BOM, разделитель «;»: так русский Excel открывает файл без «кракозябр» и без склеенных колонок.
 */
class BotUserExport
{
    private const HEADERS = [
        'ID', 'Telegram ID', 'Username', 'Имя и фамилия', 'Университет или организация', 'Телефон', 'Email',
        'О себе', 'Чего ждёт от сообщества', 'Регион', 'Статус', 'Модератор', 'Регистрация', 'Согласие на обработку данных',
        'Уровень', 'Очки', 'Факты о себе', 'Контакты закрыты', 'Новости в боте отключены', 'Последняя активность',
    ];

    public function download(): StreamedResponse
    {
        $name = 'participants-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function (): void {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, self::HEADERS, ';');

            BotUser::query()->with('facts:id,bot_user_id,body')->orderBy('id')->chunkById(200, function ($users) use ($out): void {
                foreach ($users as $user) {
                    fputcsv($out, $this->row($user), ';');
                }
            });

            fclose($out);
        }, $name, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** @return array<int, string|int> */
    public function row(BotUser $u): array
    {
        return array_map([$this, 'safe'], [
            $u->id,
            $u->telegram_id,
            $u->telegram_username ? '@'.$u->telegram_username : '',
            $u->full_name,
            $u->organization,
            $u->contact_phone,
            $u->contact_email,
            $u->description,
            $u->expectation,
            $u->region,
            $u->status === BotUser::STATUS_APPROVED ? 'активен' : 'теневой бан',
            $u->is_moderator ? 'да' : '',
            $u->approved_at?->format('Y-m-d H:i'),
            $u->consent_at?->format('Y-m-d H:i'),
            $u->level,
            $u->xp,
            $u->facts->pluck('body')->implode(' | '),
            $u->contacts_closed ? 'да' : '',
            $u->notifications_muted ? 'да' : '',
            $u->last_active_at?->format('Y-m-d H:i'),
        ]);
    }

    /** Защита от «формул» в Excel: текст, начинающийся с = + - @, получает ведущий апостроф (телефоны и @username остаются как есть). */
    private function safe(mixed $v): string
    {
        $s = trim(str_replace(["\r\n", "\r", "\n"], ' ', (string) ($v ?? '')));

        if ($s !== '' && preg_match('/^[=+\-@\t]/', $s) && ! preg_match('/^\+?[0-9][0-9\s().-]*$/', $s) && ! preg_match("/^@[A-Za-z0-9_.]+$/", $s)) {
            return "'".$s;
        }

        return $s;
    }
}
