<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Когда участник дал согласие на обработку персональных данных при регистрации в боте. */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('bot_users', 'consent_at')) {
            return;
        }

        Schema::table('bot_users', function (Blueprint $table): void {
            $table->timestamp('consent_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('bot_users', function (Blueprint $table): void {
            $table->dropColumn('consent_at');
        });
    }
};
