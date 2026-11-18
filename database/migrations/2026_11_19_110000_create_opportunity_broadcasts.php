<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Какие возможности уже разосланы в бот, чтобы каждая уходила участникам ровно один раз. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opportunity_broadcasts', function (Blueprint $table): void {
            $table->id();
            $table->string('slug')->unique();
            $table->unsignedInteger('recipients')->default(0);
            $table->unsignedInteger('failed')->default(0);
            $table->boolean('baseline')->default(false); // отмечена как «уже была», без рассылки
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opportunity_broadcasts');
    }
};
