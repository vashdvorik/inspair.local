<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiUsageLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'task', 'model', 'status', 'prompt_tokens', 'completion_tokens', 'cost', 'latency_ms', 'error',
    ];

    protected $casts = [
        'cost' => 'float',
    ];
}
