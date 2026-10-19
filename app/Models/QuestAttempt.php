<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestAttempt extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['bot_user_id', 'quest_id', 'chosen_user_id', 'correct'];

    protected $casts = ['correct' => 'boolean'];
}
