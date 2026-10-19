<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BotUser extends Model
{
    use HasFactory;
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'telegram_id',
        'telegram_username',
        'avatar_path',
        'first_name',
        'full_name',
        'description',
        'expectation',
        'status',
        'approved_at',
        'embedding',
        'embedding_updated_at',
        'embedding_model',
        'is_moderator',
        'contacts_closed',
        'quests_opt_out',
        'notifications_muted',
        'xp',
        'level',
        'quest_locked_until',
        'last_active_at',
        'last_reminded_at',
        'region',
    ];

    protected $casts = [
        'telegram_id'          => 'integer',
        'approved_at'          => 'datetime',
        'embedding'            => 'array',
        'embedding_updated_at' => 'datetime',
        'is_moderator'         => 'boolean',
        'contacts_closed'      => 'boolean',
        'quests_opt_out'       => 'boolean',
        'notifications_muted'  => 'boolean',
        'xp'                   => 'integer',
        'level'                => 'integer',
        'quest_locked_until'   => 'datetime',
        'last_active_at'       => 'datetime',
        'last_reminded_at'     => 'datetime',
    ];

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /** Можно ли писать этому участнику (есть username и контакты не закрыты). */
    public function canBeContacted(): bool
    {
        return ! $this->contacts_closed && filled($this->telegram_username);
    }

    public function facts(): HasMany
    {
        return $this->hasMany(ProfileFact::class)->orderBy('id');
    }

    public function loginTokens(): HasMany
    {
        return $this->hasMany(LoginToken::class, 'telegram_id', 'telegram_id');
    }
}
