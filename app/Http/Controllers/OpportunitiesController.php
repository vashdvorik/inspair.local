<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Support\PublicUi;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;

/**
 * «Возможности» на публичном сайте: новости о международных поездках (учебные курсы и мероприятия за рубежом).
 * Записи лежат в resources/data/opportunities.json; их создаёт tools/instagram-post.py из постов Instagram
 * (фото сохраняются на сайте), а название, страна, короткий текст и полный текст страницы ("detail") на ru/en/ro вписываются в json.
 */
class OpportunitiesController extends Controller
{
    public function index(): View
    {
        $data = PublicUi::content('opportunities');
        $items = array_map(fn (array $item) => $this->present($item), $this->items());

        usort($items, fn (array $a, array $b) => strcmp((string) $b['date'], (string) $a['date']));

        return view('public.opportunities', ['title' => $data['title'], 'intro' => $data['intro'], 'items' => $items]);
    }

    public function show(string $slug): View
    {
        $item = collect($this->items())->firstWhere('slug', $slug);
        abort_if($item === null, 404);

        return view('public.opportunity', ['o' => $this->present($item)]);
    }

    /** @return list<array<string, mixed>> */
    private function items(): array
    {
        return array_values(array_filter(
            PublicUi::content('opportunities')['items'],
            fn (array $i) => ! empty($i['slug']) && ! empty($i['name']) && ! empty($i['image']) && ! empty($i['text']),
        ));
    }

    /** @param  array<string, mixed>  $item
     *  @return array<string, mixed> */
    private function present(array $item): array
    {
        // вместо афиши-шаблона из Instagram можно показать фото города или страны ("photo" + "credit" в json)
        $item['image_url'] = PublicUi::versioned('img/opportunities/'.($item['photo'] ?? $item['image']));
        $item['dates'] = $this->range($item['start'] ?? null, $item['end'] ?? null);
        $item['deadline_text'] = $this->day($item['deadline'] ?? null);
        $item['open'] = ! empty($item['deadline']) && Carbon::parse($item['deadline'])->endOfDay()->gte(Carbon::today());

        return $item;
    }

    /** @return array{ru: string, en: string, ro: string}|null */
    private function day(?string $date): ?array
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
    private function range(?string $start, ?string $end): ?array
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
