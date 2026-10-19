<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quest extends Model
{
    public const ACTIVE = 'active';
    public const REPORTED = 'reported';
    public const REJECTED = 'rejected';

    protected $fillable = [
        'fact_id', 'subject_user_id', 'option_ids', 'question', 'explanation', 'status', 'reports_count',
    ];

    protected $casts = [
        'option_ids'  => 'array',
        'question'    => 'array',
        'explanation' => 'array',
    ];

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('status', self::ACTIVE);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(BotUser::class, 'subject_user_id');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(QuestAttempt::class);
    }
}
