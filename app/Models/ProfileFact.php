<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfileFact extends Model
{
    protected $fillable = ['bot_user_id', 'body'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(BotUser::class, 'bot_user_id');
    }
}
