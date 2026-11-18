<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\OpportunityPost;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Одна лента «Возможностей» (новости о международных поездках) для публичного сайта и для кабинета.
 * Записи лежат в базе (таблица opportunity_posts), администратор добавляет их в админке: раздел «Возможности».
 */
final class OpportunityFeed
{
    /**
     * Все записи, от новых к старым: даты и срок заявок уже разложены по трём языкам.
     *
     * @return list<array<string, mixed>>
     */
    public static function all(): array
    {
        $items = array_map(fn (array $item) => self::present($item), self::raw());
        usort($items, fn (array $a, array $b) => strcmp((string) $b['date'], (string) $a['date']));

        return $items;
    }

    /** @return array<string, mixed>|null */
    public static function find(string $slug): ?array
    {
        foreach (self::raw() as $item) {
            if ($item['slug'] === $slug) {
                return self::present($item);
            }
        }

        return null;
    }

    public static function title(): array
    {
        return PublicUi::content('opportunities')['title'];
    }

    // версия в имени ключа: после выкладки, меняющей форму записи, старый кэш на сервере не используется (иначе страницы падали бы)
    private const CACHE_KEY = 'opportunity_feed:v2';

    /** Сбросить кэш ленты (вызывается при любой правке возможностей в админке). */
    public static function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Опубликованные возможности из базы. Лента кэшируется: публичные страницы не ходят в базу на каждый запрос.
     *
     * @return list<array<string, mixed>>
     */
    private static function raw(): array
    {
        $items = Cache::rememberForever(self::CACHE_KEY, fn () => OpportunityPost::query()->where('published', true)->get()->map(function (OpportunityPost $p): array {
            // пропущенный язык заменяется английским: на сайте не бывает пустых мест
            $t = OpportunityPost::fillMissingLanguages(['country' => $p->country, 'text' => $p->text, 'detail' => $p->detail ?? []]);

            return [
                'slug'     => $p->slug,
                'name'     => $p->name,
                // название по языкам: пустой язык показывает оригинальное название
                'title'    => collect(['ru', 'en', 'ro'])->mapWithKeys(fn ($l) => [$l => filled($p->title[$l] ?? null) ? $p->title[$l] : $p->name])->all(),
                'flag'     => $p->flag,
                'country'  => $t['country'],
                'start'    => $p->start_date?->toDateString(),
                'end'      => $p->end_date?->toDateString(),
                'deadline' => $p->deadline?->toDateString(),
                'date'     => $p->posted_on?->toDateString(),
                'text'     => $t['text'],
                'detail'   => $t['detail'],
                'image'    => $p->image,
                'url'      => $p->source_url,
            ];
        })->all());

        return array_values(array_filter(
            $items,
            fn (array $i) => ! empty($i['slug']) && ! empty($i['name']) && ! empty($i['image']) && ! empty($i['text']),
        ));
    }

    /** @param  array<string, mixed>  $item
     *  @return array<string, mixed> */
    private static function present(array $item): array
    {
        // вместо афиши-шаблона из Instagram можно показать фото города или страны ("photo" + "credit" в json)
        // записи из старого кэша могли быть без названий по языкам
        $item['title'] ??= ['ru' => $item['name'], 'en' => $item['name'], 'ro' => $item['name']];
        $item['image_url'] = PublicUi::versioned('img/opportunities/'.($item['photo'] ?? $item['image']));
        $item['dates'] = self::range($item['start'] ?? null, $item['end'] ?? null);
        $item['deadline_text'] = self::day($item['deadline'] ?? null);
        $item['open'] = ! empty($item['deadline']) && Carbon::parse($item['deadline'])->endOfDay()->gte(Carbon::today());

        return $item;
    }

    /** @return array{ru: string, en: string, ro: string}|null */
    private static function day(?string $date): ?array
    {
        if (! $date) {
            return null;
        }

        $out = [];
        foreach (['ru', 'en', 'ro'] as $lang) {
            $out[$lang] = Carbon::parse($date)->locale($lang)->translatedFormat('j F Y');
        }

        return $out;
    }

    /** «1–8 декабря 2026» или «28 ноября – 3 декабря 2026» на трёх языках. */
    private static function range(?string $start, ?string $end): ?array
    {
        if (! $start) {
            return null;
        }

        $a = Carbon::parse($start);
        $b = $end ? Carbon::parse($end) : $a;

        $out = [];
        foreach (['ru', 'en', 'ro'] as $lang) {
            $x = $a->copy()->locale($lang);
            $y = $b->copy()->locale($lang);
            $out[$lang] = match (true) {
                $a->isSameDay($b) => $x->translatedFormat('j F Y'),
                $a->format('Y-m') === $b->format('Y-m') => $x->format('j').'–'.$y->translatedFormat('j F Y'),
                $a->year === $b->year => $x->translatedFormat('j F').' – '.$y->translatedFormat('j F Y'),
                default => $x->translatedFormat('j F Y').' – '.$y->translatedFormat('j F Y'),
            };
        }

        return $out;
    }
}
