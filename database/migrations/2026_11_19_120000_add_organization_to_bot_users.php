<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Университет или организация, которую участник представляет (вопрос при регистрации в боте). */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('bot_users', 'organization')) {
            return;
        }

        Schema::table('bot_users', function (Blueprint $table): void {
            $table->string('organization', 160)->nullable()->after('full_name');
        });
    }

    public function down(): void
    {
        Schema::table('bot_users', function (Blueprint $table): void {
            $table->dropColumn('organization');
        });
    }
};
