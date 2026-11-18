<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Модерации перед доступом больше нет: все участники сразу пользуются кабинетом и игрой.
 * Ожидавшие становятся обычными участниками, а отклонённые получают теневой бан (status = banned).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('bot_users')->where('status', 'pending')->update(['status' => 'approved', 'approved_at' => now()]);
        DB::table('bot_users')->where('status', 'approved')->whereNull('approved_at')->update(['approved_at' => now()]);
        DB::table('bot_users')->where('status', 'rejected')->update(['status' => 'banned']);
    }

    public function down(): void
    {
        DB::table('bot_users')->where('status', 'banned')->update(['status' => 'rejected']);
    }
};
