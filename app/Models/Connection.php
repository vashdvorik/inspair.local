<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Connection extends Model
{
    protected $fillable = ['user_id', 'target_id', 'source', 'mutual', 'target_notified_at'];

    protected $casts = ['mutual' => 'boolean', 'target_notified_at' => 'datetime'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(BotUser::class, 'user_id');
    }

    public function target(): BelongsTo
    {
        return $this->belongsTo(BotUser::class, 'target_id');
    }
}
