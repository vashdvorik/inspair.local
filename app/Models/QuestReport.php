<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestReport extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['quest_id', 'bot_user_id', 'reason'];
}
