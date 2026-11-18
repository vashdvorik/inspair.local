<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * «Возможности» (поездки и курсы за рубежом) теперь хранятся в базе, и администратор добавляет их в админке.
 * При создании таблицы подтягиваются записи из resources/data/opportunities.json, чтобы ничего не потерялось.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opportunity_posts', function (Blueprint $table): void {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('flag', 16)->nullable();
            $table->json('country');                       // {ru, en, ro}
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->date('deadline')->nullable();          // срок подачи заявок
            $table->date('posted_on');                     // по ней сортируется лента (новые сверху)
            $table->json('text');                          // краткое описание для карточки {ru, en, ro}
            $table->json('detail')->nullable();            // полный текст {ru|en|ro: {lead, explore[], format, benefits[], audience, note}}
            $table->string('image');                       // файл в public/img/opportunities
            $table->string('source_url')->nullable();
            $table->boolean('published')->default(true);
            $table->timestamps();
        });

        $file = resource_path('data/opportunities.json');
        if (! is_file($file)) {
            return;
        }

        $items = json_decode((string) file_get_contents($file), true)['items'] ?? [];
        foreach ($items as $i) {
            if (empty($i['slug']) || empty($i['name']) || empty($i['image']) || empty($i['text'])) {
                continue;
            }
            DB::table('opportunity_posts')->insert([
                'slug'       => $i['slug'],
                'name'       => $i['name'],
                'flag'       => $i['flag'] ?? null,
                'country'    => json_encode($i['country'] ?? [], JSON_UNESCAPED_UNICODE),
                'start_date' => $i['start'] ?? null,
                'end_date'   => $i['end'] ?? null,
                'deadline'   => $i['deadline'] ?? null,
                'posted_on'  => $i['date'] ?? now()->toDateString(),
                'text'       => json_encode($i['text'], JSON_UNESCAPED_UNICODE),
                'detail'     => isset($i['detail']) ? json_encode($i['detail'], JSON_UNESCAPED_UNICODE) : null,
                'image'      => $i['photo'] ?? $i['image'],
                'source_url' => $i['url'] ?? null,
                'published'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('opportunity_posts');
    }
};
