<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Предупреждения нейросети о подозрительных текстах участников и отметка «что уже проверено».
 *
 * Миграцию можно запускать повторно (после неудачного запуска). Внешнего ключа на bot_users нет: на части хостингов
 * сервер его не принимает (ошибка 1824), поэтому при удалении участника строки чистит BotUser::deleting().
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['moderated_hash' => fn (Blueprint $t) => $t->string('moderated_hash', 40)->nullable(), 'moderated_at' => fn (Blueprint $t) => $t->timestamp('moderated_at')->nullable()] as $name => $add) {
            if (! Schema::hasColumn('bot_users', $name)) {
                Schema::table('bot_users', fn (Blueprint $t) => $add($t));
            }
        }

        // остаток неудачного запуска: пустую таблицу пересоздаём начисто
        if (Schema::hasTable('moderation_flags')) {
            if (DB::table('moderation_flags')->count() > 0) {
                return;
            }
            Schema::drop('moderation_flags');
        }

        Schema::create('moderation_flags', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('bot_user_id')->index();
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
        foreach (['moderated_hash', 'moderated_at'] as $column) {
            if (Schema::hasColumn('bot_users', $column)) {
                Schema::table('bot_users', fn (Blueprint $t) => $t->dropColumn($column));
            }
        }
    }
};
