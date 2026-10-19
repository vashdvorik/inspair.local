<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Переопределения настроек задач из админки (по умолчанию — config/ai.php)
        Schema::create('ai_task_settings', function (Blueprint $table) {
            $table->id();
            $table->string('task', 64)->unique();
            $table->string('model')->nullable();
            $table->string('fallback_model')->nullable();
            $table->decimal('temperature', 3, 2)->nullable();
            $table->unsignedInteger('max_tokens')->nullable();
            $table->unsignedInteger('daily_call_limit')->nullable();
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });

        // Журнал обращений к моделям: расход и ошибки
        Schema::create('ai_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->string('task', 64);
            $table->string('model');
            $table->string('status', 16); // ok | fallback | error
            $table->unsignedInteger('prompt_tokens')->default(0);
            $table->unsignedInteger('completion_tokens')->default(0);
            $table->decimal('cost', 12, 6)->nullable();
            $table->unsignedInteger('latency_ms')->default(0);
            $table->text('error')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['task', 'created_at']);
            $table->index('created_at');
        });

        // Эмбеддинги разных моделей несравнимы — помним, какой моделью посчитан вектор
        Schema::table('bot_users', function (Blueprint $table) {
            $table->string('embedding_model')->nullable()->after('embedding_updated_at');
        });
    }

    public function down(): void
    {
        Schema::table('bot_users', function (Blueprint $table) {
            $table->dropColumn('embedding_model');
        });
        Schema::dropIfExists('ai_usage_logs');
        Schema::dropIfExists('ai_task_settings');
    }
};
