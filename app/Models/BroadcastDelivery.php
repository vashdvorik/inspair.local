<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BroadcastDelivery extends Model
{
    protected $fillable = ['broadcast_id', 'telegram_id', 'delivered', 'error'];

    protected $casts = ['delivered' => 'boolean'];
}
