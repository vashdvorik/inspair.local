<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Сообщение администратора участникам в бот. */
class Broadcast extends Model
{
    public const DRAFT = 'draft';
    public const SCHEDULED = 'scheduled';
    public const SENDING = 'sending';
    public const SENT = 'sent';

    public const AUDIENCE_NOTIFY = 'notify';
    public const AUDIENCE_ALL = 'all';

    /** Страницы кабинета, которые кнопка может открывать внутри мини-приложения. */
    public const CABINET_PAGES = [
        ''               => 'Главная кабинета',
        '/opportunities' => 'Возможности',
        '/people'        => 'Участники',
        '/game'          => 'Игра',
        '/profile'       => 'Мой профиль',
    ];

    protected $fillable = ['title', 'body', 'image_path', 'buttons', 'audience', 'status', 'scheduled_at', 'sent_at', 'recipients_count', 'failed_count'];

    protected $casts = [
        'buttons'      => 'array',
        'scheduled_at' => 'datetime',
        'sent_at'      => 'datetime',
    ];

    public function deliveries(): HasMany
    {
        return $this->hasMany(BroadcastDelivery::class);
    }

    public function isEditable(): bool
    {
        return in_array($this->status, [self::DRAFT, self::SCHEDULED], true);
    }

    /** Кому уходит рассылка. */
    public function recipients()
    {
        $q = BotUser::query()->whereNotNull('telegram_id');

        return $this->audience === self::AUDIENCE_ALL ? $q : $q->where('notifications_muted', false);
    }

    /**
     * Кнопки в виде, удобном для отправки: [['label' => ..., 'url' => ..., 'web_app' => bool], ...].
     * Страницы кабинета открываются внутри Telegram (мини-приложение), остальные ссылки как обычные.
     */
    public function keyboard(): array
    {
        $rows = [];
        foreach ((array) $this->buttons as $b) {
            $label = trim((string) ($b['label'] ?? ''));
            if ($label === '') {
                continue;
            }
            if (($b['kind'] ?? 'url') === 'cabinet') {
                $rows[] = ['label' => $label, 'url' => rtrim((string) config('app.url'), '/').'/app/account'.($b['page'] ?? ''), 'web_app' => true];
            } elseif (filled($b['url'] ?? null)) {
                $rows[] = ['label' => $label, 'url' => trim((string) $b['url']), 'web_app' => false];
            }
        }

        return $rows;
    }
}
