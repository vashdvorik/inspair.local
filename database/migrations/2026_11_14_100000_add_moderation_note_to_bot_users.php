<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Сообщение модератора участнику: показывается в кабинете, когда доступ к игре и участникам закрыт
        if (! Schema::hasColumn('bot_users', 'moderation_note')) {
            Schema::table('bot_users', function (Blueprint $table) {
                $table->text('moderation_note')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('bot_users', 'moderation_note')) {
            Schema::table('bot_users', function (Blueprint $table) {
                $table->dropColumn('moderation_note');
            });
        }
    }
};
