<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Support\PublicUi;
use Illuminate\Contracts\View\View;

/**
 * «Публикации»: список изданий и страница-читалка с листанием (разворот книги).
 * Издания описаны в resources/data/publications.json, страницы лежат готовыми WebP в public/img/publications/<slug>/
 * (их делает tools/pdf-to-pages.py из PDF один раз на компьютере: посетителю уходят только лёгкие картинки).
 */
class PublicationsController extends Controller
{
    public function index(): View
    {
        return view('public.publications', [
            'site' => PublicUi::content('site'),
            'items' => array_map(fn (array $item) => $this->present($item), $this->items()),
        ]);
    }

    public function show(string $slug): View
    {
        $item = collect($this->items())->firstWhere('slug', $slug);
        abort_if($item === null, 404);

        $item = $this->present($item);
        $dir = 'img/publications/'.$slug;
        $stamp = (is_file(public_path($dir.'/cover.webp')) ? filemtime(public_path($dir.'/cover.webp')) : 0).'-'.config('app.cache_epoch');

        // страницы, убранные из издания ("skip" в publications.json), в читалку не попадают
        $numbers = array_values(array_diff(range(1, (int) $item['pages']), array_map('intval', $item['skip'] ?? [])));
        $item['page_urls'] = array_map(
            fn (int $n) => asset(sprintf('%s/%03d.webp', $dir, $n)).'?v='.$stamp,
            $numbers,
        );

        return view('public.publication', ['item' => $item]);
    }

    /** @return list<array<string, mixed>> */
    private function items(): array
    {
        return array_values(array_filter(
            PublicUi::content('publications')['items'] ?? [],
            fn (array $item) => ! empty($item['slug']) && (int) ($item['pages'] ?? 0) > 0,
        ));
    }

    /** @param  array<string, mixed>  $item
     *  @return array<string, mixed> */
    private function present(array $item): array
    {
        $item['cover_url'] = PublicUi::versioned('img/publications/'.$item['slug'].'/'.($item['cover'] ?? 'cover.webp'));
        $item['ratio'] = round((float) ($item['ratio'] ?? 0.7071), 4);

        return $item;
    }
}
