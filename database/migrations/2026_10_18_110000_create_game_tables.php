<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bot_users', function (Blueprint $table) {
            $table->boolean('is_moderator')->default(false);
            $table->boolean('contacts_closed')->default(false);
            $table->boolean('quests_opt_out')->default(false);
            $table->boolean('notifications_muted')->default(false);
            $table->unsignedInteger('xp')->default(0);
            $table->unsignedTinyInteger('level')->default(1);
            $table->timestamp('quest_locked_until')->nullable();
            $table->timestamp('last_active_at')->nullable();
            $table->timestamp('last_reminded_at')->nullable();
        });

        // Короткие факты о себе: из них ИИ делает загадки
        Schema::create('profile_facts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bot_user_id')->constrained('bot_users')->cascadeOnDelete();
            $table->string('body', 300);
            $table->timestamps();
        });

        // Журнал начислений очков. Уникальный ключ делает разовые награды идемпотентными.
        Schema::create('xp_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bot_user_id')->constrained('bot_users')->cascadeOnDelete();
            $table->string('type', 40);
            $table->string('ref', 64)->default('');
            $table->integer('points');
            $table->json('meta')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['bot_user_id', 'type', 'ref']);
            $table->index(['type', 'created_at']);
        });

        // Нити сети: user_id добавил target_id (за верный ответ); mutual — взаимно
        Schema::create('connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('bot_users')->cascadeOnDelete();
            $table->foreignId('target_id')->constrained('bot_users')->cascadeOnDelete();
            $table->string('source', 16)->default('quest'); // quest | reciprocate
            $table->boolean('mutual')->default(false);
            $table->timestamp('target_notified_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'target_id']);
            $table->index('target_id');
        });

        Schema::create('quests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fact_id')->nullable()->constrained('profile_facts')->nullOnDelete();
            $table->foreignId('subject_user_id')->constrained('bot_users')->cascadeOnDelete();
            $table->json('option_ids');      // три id участников, один из них subject
            $table->json('question');        // {ru,en,ro}
            $table->json('explanation');     // {ru,en,ro}
            $table->string('status', 16)->default('active'); // active | reported | rejected
            $table->unsignedSmallInteger('reports_count')->default(0);
            $table->timestamps();

            $table->index(['status', 'subject_user_id']);
        });

        Schema::create('quest_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bot_user_id')->constrained('bot_users')->cascadeOnDelete();
            $table->foreignId('quest_id')->constrained('quests')->cascadeOnDelete();
            $table->unsignedBigInteger('chosen_user_id');
            $table->boolean('correct');
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['bot_user_id', 'quest_id']);
        });

        Schema::create('quest_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quest_id')->constrained('quests')->cascadeOnDelete();
            $table->foreignId('bot_user_id')->constrained('bot_users')->cascadeOnDelete();
            $table->string('reason', 200)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['quest_id', 'bot_user_id']);
        });

        Schema::create('game_settings', function (Blueprint $table) {
            $table->string('key', 64)->primary();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_settings');
        Schema::dropIfExists('quest_reports');
        Schema::dropIfExists('quest_attempts');
        Schema::dropIfExists('quests');
        Schema::dropIfExists('connections');
        Schema::dropIfExists('xp_events');
        Schema::dropIfExists('profile_facts');
        Schema::table('bot_users', function (Blueprint $table) {
            $table->dropColumn([
                'is_moderator', 'contacts_closed', 'quests_opt_out', 'notifications_muted',
                'xp', 'level', 'quest_locked_until', 'last_active_at', 'last_reminded_at',
            ]);
        });
    }
};
