<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Предупреждение нейросети: в тексте участника что-то подозрительное. Решение всегда за администратором. */
class ModerationFlag extends Model
{
    public const OPEN = 'open';
    public const DISMISSED = 'dismissed';
    public const BANNED = 'banned';

    public const CATEGORIES = [
        'sexual' => 'Сексуальный контент',
        'abuse'  => 'Грубость и оскорбления',
        'hate'   => 'Ненависть и дискриминация',
        'scam'   => 'Реклама, спам, мошенничество',
        'fake'   => 'Бессмыслица или фейк',
        'other'  => 'Другое',
    ];

    public const FIELDS = [
        'name'      => 'Имя',
        'about'     => 'О себе',
        'organization' => 'Организация',
        'instagram' => 'Instagram (старое)',
        'fact'      => 'Факт о себе',
    ];

    protected $fillable = ['bot_user_id', 'field', 'excerpt', 'excerpt_hash', 'category', 'severity', 'reason', 'status', 'reviewed_at'];

    protected $casts = ['reviewed_at' => 'datetime'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(BotUser::class, 'bot_user_id');
    }
}
