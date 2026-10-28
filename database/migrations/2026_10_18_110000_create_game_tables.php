<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Game tables. The migration is re-runnable (a failed run can simply be started again) and tolerant:
 * if the server refuses foreign keys to bot_users (error 1824/1215 on some hosts), the tables are
 * created without foreign keys and BotUser::deleting() removes related rows instead.
 */
return new class extends Migration
{
    public function up(): void
    {
        $columns = [
            'is_moderator'        => fn (Blueprint $t) => $t->boolean('is_moderator')->default(false),
            'contacts_closed'     => fn (Blueprint $t) => $t->boolean('contacts_closed')->default(false),
            'quests_opt_out'      => fn (Blueprint $t) => $t->boolean('quests_opt_out')->default(false),
            'notifications_muted' => fn (Blueprint $t) => $t->boolean('notifications_muted')->default(false),
            'xp'                  => fn (Blueprint $t) => $t->unsignedInteger('xp')->default(0),
            'level'               => fn (Blueprint $t) => $t->unsignedTinyInteger('level')->default(1),
            'quest_locked_until'  => fn (Blueprint $t) => $t->timestamp('quest_locked_until')->nullable(),
            'last_active_at'      => fn (Blueprint $t) => $t->timestamp('last_active_at')->nullable(),
            'last_reminded_at'    => fn (Blueprint $t) => $t->timestamp('last_reminded_at')->nullable(),
        ];
        foreach ($columns as $name => $add) {
            if (! Schema::hasColumn('bot_users', $name)) {
                Schema::table('bot_users', fn (Blueprint $t) => $add($t));
            }
        }

        // Короткие факты о себе: из них ИИ делает загадки
        $this->make('profile_facts', function (Blueprint $table, bool $fk) {
            $table->id();
            $this->ref($table, 'bot_user_id', 'bot_users', $fk);
            $table->string('body', 300);
            $table->timestamps();
        });

        // Журнал начислений очков. Уникальный ключ делает разовые награды идемпотентными.
        $this->make('xp_events', function (Blueprint $table, bool $fk) {
            $table->id();
            $this->ref($table, 'bot_user_id', 'bot_users', $fk);
            $table->string('type', 40);
            $table->string('ref', 64)->default('');
            $table->integer('points');
            $table->json('meta')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['bot_user_id', 'type', 'ref']);
            $table->index(['type', 'created_at']);
        });

        // Нити сети: user_id добавил target_id (за верный ответ); mutual — взаимно
        $this->make('connections', function (Blueprint $table, bool $fk) {
            $table->id();
            $this->ref($table, 'user_id', 'bot_users', $fk);
            $this->ref($table, 'target_id', 'bot_users', $fk);
            $table->string('source', 16)->default('quest'); // quest | reciprocate
            $table->boolean('mutual')->default(false);
            $table->timestamp('target_notified_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'target_id']);
            $table->index('target_id');
        });

        $this->make('quests', function (Blueprint $table, bool $fk) {
            $table->id();
            $table->unsignedBigInteger('fact_id')->nullable()->index();
            $this->ref($table, 'subject_user_id', 'bot_users', $fk);
            $table->json('option_ids');      // три id участников, один из них subject
            $table->json('question');        // {ru,en,ro}
            $table->json('explanation');     // {ru,en,ro}
            $table->string('status', 16)->default('active'); // active | reported | rejected
            $table->unsignedSmallInteger('reports_count')->default(0);
            $table->timestamps();

            $table->index(['status', 'subject_user_id']);
        });

        $this->make('quest_attempts', function (Blueprint $table, bool $fk) {
            $table->id();
            $this->ref($table, 'bot_user_id', 'bot_users', $fk);
            $this->ref($table, 'quest_id', 'quests', $fk);
            $table->unsignedBigInteger('chosen_user_id');
            $table->boolean('correct');
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['bot_user_id', 'quest_id']);
        });

        $this->make('quest_reports', function (Blueprint $table, bool $fk) {
            $table->id();
            $this->ref($table, 'quest_id', 'quests', $fk);
            $this->ref($table, 'bot_user_id', 'bot_users', $fk);
            $table->string('reason', 200)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['quest_id', 'bot_user_id']);
        });

        $this->make('game_settings', function (Blueprint $table, bool $fk) {
            $table->string('key', 64)->primary();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['game_settings', 'quest_reports', 'quest_attempts', 'quests', 'connections', 'xp_events', 'profile_facts'] as $table) {
            Schema::dropIfExists($table);
        }
        foreach (['is_moderator', 'contacts_closed', 'quests_opt_out', 'notifications_muted', 'xp', 'level', 'quest_locked_until', 'last_active_at', 'last_reminded_at'] as $column) {
            if (Schema::hasColumn('bot_users', $column)) {
                Schema::table('bot_users', fn (Blueprint $t) => $t->dropColumn($column));
            }
        }
    }

    /** Создать таблицу, если её нет: сначала с внешними ключами, при отказе сервера без них. */
    private function make(string $name, callable $define): void
    {
        if (Schema::hasTable($name)) {
            return;
        }

        try {
            Schema::create($name, fn (Blueprint $table) => $define($table, true));
        } catch (QueryException) {
            Schema::dropIfExists($name);
            Schema::create($name, fn (Blueprint $table) => $define($table, false));
        }
    }

    private function ref(Blueprint $table, string $column, string $on, bool $fk): void
    {
        if ($fk) {
            $table->foreignId($column)->constrained($on)->cascadeOnDelete();
        } else {
            $table->unsignedBigInteger($column)->index();
        }
    }
};
