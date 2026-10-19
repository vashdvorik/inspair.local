<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiTaskSetting extends Model
{
    protected $fillable = [
        'task', 'model', 'fallback_model', 'temperature', 'max_tokens', 'daily_call_limit', 'enabled',
    ];

    protected $casts = [
        'temperature'      => 'float',
        'max_tokens'       => 'integer',
        'daily_call_limit' => 'integer',
        'enabled'          => 'boolean',
    ];
}
