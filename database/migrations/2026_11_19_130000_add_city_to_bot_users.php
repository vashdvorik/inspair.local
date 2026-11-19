<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Город участника (вопрос при регистрации в боте). */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('bot_users', 'city')) {
            return;
        }

        Schema::table('bot_users', function (Blueprint $table): void {
            $table->string('city', 80)->nullable()->after('organization');
        });
    }

    public function down(): void
    {
        Schema::table('bot_users', function (Blueprint $table): void {
            $table->dropColumn('city');
        });
    }
};
