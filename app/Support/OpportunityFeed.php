<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Carbon;

/**
 * Одна лента «Возможностей» (новости о международных поездках) для публичного сайта и для кабинета.
 * Записи лежат в resources/data/opportunities.json (их создаёт tools/instagram-post.py).
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

    /** @return list<array<string, mixed>> */
    private static function raw(): array
    {
        return array_values(array_filter(
            PublicUi::content('opportunities')['items'],
            fn (array $i) => ! empty($i['slug']) && ! empty($i['name']) && ! empty($i['image']) && ! empty($i['text']),
        ));
    }

    /** @param  array<string, mixed>  $item
     *  @return array<string, mixed> */
    private static function present(array $item): array
    {
        // вместо афиши-шаблона из Instagram можно показать фото города или страны ("photo" + "credit" в json)
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
