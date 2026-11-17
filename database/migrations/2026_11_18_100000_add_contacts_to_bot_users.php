<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Дополнительные контакты в профиле: телефон, почта, Instagram. Видны одобренным участникам, если контакты не закрыты. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bot_users', function (Blueprint $table): void {
            $table->string('contact_phone', 40)->nullable()->after('telegram_username');
            $table->string('contact_email', 120)->nullable()->after('contact_phone');
            $table->string('contact_instagram', 60)->nullable()->after('contact_email');
        });
    }

    public function down(): void
    {
        Schema::table('bot_users', function (Blueprint $table): void {
            $table->dropColumn(['contact_phone', 'contact_email', 'contact_instagram']);
        });
    }
};
