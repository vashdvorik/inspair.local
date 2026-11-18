<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Название возможности по языкам {ru, en, ro}. Пустой язык показывается оригинальным названием (поле name). */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('opportunity_posts', 'title')) {
            Schema::table('opportunity_posts', function (Blueprint $table): void {
                $table->json('title')->nullable()->after('name');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('opportunity_posts', 'title')) {
            Schema::table('opportunity_posts', fn (Blueprint $table) => $table->dropColumn('title'));
        }
    }
};
