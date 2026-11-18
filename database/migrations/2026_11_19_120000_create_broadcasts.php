<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Сообщения, которые администратор сам рассылает участникам в бот, и учёт доставки каждому. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('broadcasts', function (Blueprint $table): void {
            $table->id();
            $table->string('title');                       // название для админки, участники его не видят
            $table->text('body');                          // текст сообщения (Telegram HTML)
            $table->string('image_path')->nullable();      // картинка к сообщению
            $table->json('buttons')->nullable();           // [{label, kind: url|cabinet, url|page}]
            $table->string('audience')->default('notify'); // notify: кто не отключил уведомления; all: все
            $table->string('status')->default('draft');    // draft, scheduled, sending, sent
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->unsignedInteger('recipients_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->timestamps();
        });

        Schema::create('broadcast_deliveries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('broadcast_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('telegram_id');
            $table->boolean('delivered')->default(false);
            $table->string('error')->nullable();
            $table->timestamps();
            $table->unique(['broadcast_id', 'telegram_id']); // повторный запуск после сбоя не шлёт дважды
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('broadcast_deliveries');
        Schema::dropIfExists('broadcasts');
    }
};
