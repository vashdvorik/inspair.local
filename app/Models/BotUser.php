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
    public const STATUS_APPROVED = 'approved';
    /** Теневой бан: участник пользуется всем как обычно, но другие его не видят (списки, поиск, совпадения, игра). */
    public const STATUS_BANNED = 'banned';

    protected $fillable = [
        'telegram_id',
        'telegram_username',
        'contact_phone',
        'contact_email',
        'avatar_path',
        'first_name',
        'full_name',
        'organization',
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
        'moderation_note',
        'consent_at',
    ];

    protected $casts = [
        'telegram_id'          => 'integer',
        'approved_at'          => 'datetime',
        'consent_at'           => 'datetime',
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

    /**
     * Игровые данные удаляются вместе с участником. Внешние ключи делают это сами,
     * но на серверах, где ключи не создались, это делает код.
     */
    protected static function booted(): void
    {
        static::deleting(function (self $user): void {
            $questIds = \Illuminate\Support\Facades\DB::table('quests')->where('subject_user_id', $user->id)->pluck('id');
            \Illuminate\Support\Facades\DB::table('quest_attempts')->where('bot_user_id', $user->id)->orWhereIn('quest_id', $questIds)->delete();
            \Illuminate\Support\Facades\DB::table('quest_reports')->where('bot_user_id', $user->id)->orWhereIn('quest_id', $questIds)->delete();
            \Illuminate\Support\Facades\DB::table('quests')->where('subject_user_id', $user->id)->delete();
            \Illuminate\Support\Facades\DB::table('connections')->where('user_id', $user->id)->orWhere('target_id', $user->id)->delete();
            \Illuminate\Support\Facades\DB::table('xp_events')->where('bot_user_id', $user->id)->delete();
            \Illuminate\Support\Facades\DB::table('profile_facts')->where('bot_user_id', $user->id)->delete();
            \Illuminate\Support\Facades\DB::table('moderation_flags')->where('bot_user_id', $user->id)->delete();
        });
    }

    /** Видимые участники: обычные, без забаненных. Все списки, поиск и игра берут людей отсюда. */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeBanned(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_BANNED);
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isBanned(): bool
    {
        return $this->status === self::STATUS_BANNED;
    }

    /** Можно ли писать этому участнику (есть username и контакты не закрыты). */
    public function canBeContacted(): bool
    {
        return ! $this->contacts_closed && filled($this->telegram_username);
    }

    public function moderationFlags(): HasMany
    {
        return $this->hasMany(ModerationFlag::class);
    }

    public function facts(): HasMany
    {
        return $this->hasMany(ProfileFact::class)->orderBy('id');
    }

    public function loginTokens(): HasMany
    {
        return $this->hasMany(LoginToken::class, 'telegram_id', 'telegram_id');
    }

    /** Адрес аватарки в кабинете (null, если фото ещё нет). ?v= меняется, когда фото обновилось. */
    public function avatarUrl(): ?string
    {
        if (! $this->avatar_path) {
            return null;
        }

        return route('account.avatar', $this->id).'?v='.($this->updated_at?->timestamp ?? 0);
    }
}
