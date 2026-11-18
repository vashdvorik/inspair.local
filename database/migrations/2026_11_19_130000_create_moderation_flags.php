<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Предупреждения нейросети о подозрительных текстах участников и отметка «что уже проверено». */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bot_users', function (Blueprint $table): void {
            $table->string('moderated_hash', 40)->nullable(); // хеш проверенных текстов: изменились ли они с прошлой проверки
            $table->timestamp('moderated_at')->nullable();
        });

        Schema::create('moderation_flags', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('bot_user_id')->constrained('bot_users')->cascadeOnDelete();
            $table->string('field');                        // name, about, instagram, fact
            $table->text('excerpt');                        // сам текст, который вызвал подозрение
            $table->string('excerpt_hash', 40);             // чтобы не создавать повторно одно и то же предупреждение
            $table->string('category');                     // sexual, abuse, hate, scam, fake, other
            $table->string('severity')->default('medium');  // low, medium, high
            $table->string('reason', 500)->nullable();      // короткое пояснение нейросети
            $table->string('status')->default('open');      // open, dismissed, banned
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('moderation_flags');
        Schema::table('bot_users', function (Blueprint $table): void {
            $table->dropColumn(['moderated_hash', 'moderated_at']);
        });
    }
};
