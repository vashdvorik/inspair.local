<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

/** Public site built from the customer brief: pages, menu, redirects, stubs, no registration buttons. */
class PublicSiteTest extends TestCase
{
    /** @return array<string, array{0: string}> */
    public static function pages(): array
    {
        return array_combine(
            $paths = ['/', '/events/hackathon-2026', '/events/schools-2025', '/events/schools-2025/school-1', '/events/schools-2025/school-2',
                '/events/schools-2025/school-3', '/events/school-2023', '/experts', '/photo', '/video', '/publications', '/partners'],
            array_map(fn ($p) => [$p], $paths),
        );
    }

    /** @dataProvider pages */
    public function test_page_renders(string $path): void
    {
        $this->get($path)->assertOk();
    }

    public function test_english_is_the_original_language(): void
    {
        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('<html lang="en">', $html);
        // the three language buttons: English first
        $this->assertLessThan(strpos($html, 'id="btn-ru"'), strpos($html, 'id="btn-en"'));
        $this->assertLessThan(strpos($html, 'id="btn-ro"'), strpos($html, 'id="btn-ru"'));
    }

    public function test_menu_matches_the_brief(): void
    {
        $html = $this->get('/')->getContent();
        foreach (['Home', 'Events', 'Hackathon 2026', 'Schools 2025', 'School 2023', 'Experts', 'Photo', 'Video', 'Partners', 'Publications'] as $item) {
            $this->assertStringContainsString($item, $html, "menu item {$item}");
        }
        foreach (['/events/hackathon-2026', '/events/schools-2025', '/events/school-2023', '/photo', '/video', '/publications'] as $href) {
            $this->assertStringContainsString($href, $html);
        }
    }

    public function test_home_has_the_blocks_from_the_brief(): void
    {
        $html = $this->get('/')->getContent();
        foreach ([
            'Connecting Minds. Building Trust. Creating Change.', 'class="ticker"', 'INSPIRE – Turning Ideas into Impact',
            'Our Story: From Education to Impact', 'Our Values', 'Values &amp; Core Areas', 'Who Can Join INSPIRE?', 'Key Features of the INSPIRE Events',
            'INSPIRE Academy Alumni: Your Path to Success', 'Expert Mentors', 'Join now', 'img/hero/group-soft.webp',
        ] as $needle) {
            $this->assertStringContainsString($needle, $html, $needle);
        }
    }

    public function test_old_addresses_redirect_permanently(): void
    {
        $this->get('/academy-2025')->assertRedirect('/events/schools-2025')->assertStatus(301);
        $this->get('/academy-2023')->assertRedirect('/events/school-2023')->assertStatus(301);
        $this->get('/academy-2025/school-2')->assertRedirect('/events/schools-2025/school-2')->assertStatus(301);
    }

    public function test_hackathon_and_publications_are_stubs(): void
    {
        $this->get('/events/hackathon-2026')->assertOk()->assertSee('Information will appear here very soon', false);
        $this->get('/publications')->assertOk()->assertSee('We are preparing the Publications section', false);
    }

    public function test_events_have_the_correct_dates(): void
    {
        $html = $this->get('/events/schools-2025')->getContent();
        foreach (['15–17 May 2025', '31 October – 2 November 2025', '19–22 November 2025', 'December 2025', 'Digital Park'] as $needle) {
            $this->assertStringContainsString($needle, $html, $needle);
        }
        $this->assertStringContainsString('14–18 September 2023', $this->get('/events/school-2023')->getContent());
        $this->assertStringContainsString('Vadul lui Vodă', $this->get('/events/school-2023')->getContent());
    }

    public function test_no_registration_buttons_for_finished_events(): void
    {
        foreach (['/events/schools-2025', '/events/schools-2025/school-1', '/events/schools-2025/school-3', '/events/school-2023'] as $path) {
            $html = preg_replace('#<script.*?</script>#s', '', $this->get($path)->getContent());
            $this->assertStringNotContainsString('docs.google.com/forms', $html, $path);
            $this->assertStringNotContainsString('id="deadlines"', $html, $path);
            $this->assertDoesNotMatchRegularExpression('/>\s*Register\s*</', $html, $path);
        }
    }

    public function test_photo_page_lists_all_albums_and_images_exist(): void
    {
        $html = $this->get('/photo')->assertOk()->getContent();
        $gallery = json_decode((string) file_get_contents(resource_path('data/gallery.json')), true);
        foreach (['final-conference', 'school-1-chisinau', 'school-2-comrat', 'school-3-tiraspol', 'organizations', 'school-2023'] as $album) {
            $this->assertStringContainsString('id="' . $album . '"', $html);
            $this->assertNotEmpty($gallery[$album]);
            $first = $gallery[$album][0]['f'];
            $this->assertFileExists(public_path("img/gallery/{$album}/{$first}.webp"));
            $this->assertFileExists(public_path("img/gallery/{$album}/{$first}-t.webp"));
        }
        $this->assertSame(380, array_sum(array_map('count', $gallery)));
    }

    public function test_customer_folder_is_not_inside_public(): void
    {
        $this->assertDirectoryDoesNotExist(public_path('user'));
        $this->assertStringContainsString('/user', (string) file_get_contents(base_path('.gitignore')));
    }
}
