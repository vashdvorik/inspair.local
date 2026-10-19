<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class XpEvent extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['bot_user_id', 'type', 'ref', 'points', 'meta'];

    protected $casts = ['meta' => 'array', 'points' => 'integer'];
}
