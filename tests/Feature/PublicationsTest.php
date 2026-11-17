<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

/** Публикации: список изданий и читалка с листанием. */
class PublicationsTest extends TestCase
{
    public function test_list_shows_the_collection_with_a_cover_and_a_link(): void
    {
        $this->get(route('publications'))
            ->assertOk()
            ->assertSee('pub-card', false)
            ->assertSee(route('publications.show', 'inspire-2025-collection'), false)
            ->assertSee('img/publications/inspire-2025-collection/cover.webp', false);
    }

    public function test_reader_gets_every_page_address_and_the_controls(): void
    {
        $html = $this->get(route('publications.show', 'inspire-2025-collection'))->assertOk()->getContent();

        $this->assertStringContainsString('data-flip', $html);
        $this->assertStringContainsString('data-flip-zoom-in', $html);
        $this->assertStringContainsString('data-flip-full', $html);
        $this->assertStringContainsString('design/page-flip.browser.js', $html);
        $this->assertStringContainsString('design/flipbook.js', $html);

        // адреса страниц лежат в data-pages: первая и последняя
        $this->assertStringContainsString('img\/publications\/inspire-2025-collection\/001.webp', html_entity_decode($html));
        $this->assertStringContainsString('img\/publications\/inspire-2025-collection\/228.webp', html_entity_decode($html));
    }

    public function test_page_images_exist_for_every_page_in_the_data(): void
    {
        $items = json_decode((string) file_get_contents(resource_path('data/publications.json')), true)['items'];

        foreach ($items as $item) {
            $this->assertFileExists(public_path('img/publications/'.$item['slug'].'/cover.webp'));
            for ($n = 1; $n <= $item['pages']; $n++) {
                if (in_array($n, $item['skip'] ?? [], true)) {
                    $this->assertFileDoesNotExist(public_path(sprintf('img/publications/%s/%03d.webp', $item['slug'], $n)));

                    continue;
                }
                $this->assertFileExists(public_path(sprintf('img/publications/%s/%03d.webp', $item['slug'], $n)), 'нет страницы '.$n);
            }
        }
    }

    public function test_unknown_publication_is_404(): void
    {
        $this->get('/publications/no-such-book')->assertNotFound();
    }
}
