<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

/** «Возможности»: новости о поездках вместо ссылки на внешний сайт. */
class OpportunitiesPageTest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    public function test_page_lists_trips_with_local_photos_and_instagram_links(): void
    {
        $this->get(route('opportunities'))
            ->assertOk()
            ->assertSee('opp-row', false)
            ->assertSee('E-motion', false)
            ->assertSee('img/opportunities/DejdWEjNR2m-photo.webp', false)
            ->assertSee(route('opportunities.show', 'emotion-armenia'), false)
            ->assertSee('1–8 декабря 2026', false);
    }

    public function test_each_opportunity_has_a_full_page_in_three_languages(): void
    {
        $data = json_decode((string) file_get_contents(resource_path('data/opportunities.json')), true);

        foreach ($data['items'] as $item) {
            $this->assertNotEmpty($item['slug'], $item['code']);
            foreach (['ru', 'en', 'ro'] as $lang) {
                $this->assertNotEmpty($item['detail'][$lang]['lead'] ?? '', $item['code'].' '.$lang);
                $this->assertNotEmpty($item['detail'][$lang]['audience'] ?? '', $item['code'].' '.$lang);
            }
            $this->get(route('opportunities.show', $item['slug']))->assertOk()->assertSee(e($item['name']), false);
        }

        $this->get('/opportunities/no-such-trip')->assertNotFound();
    }

    public function test_cards_do_not_show_the_closed_applications_notice(): void
    {
        $this->get(route('opportunities'))->assertDontSee('Applications are closed')->assertDontSee('Приём заявок завершён')->assertDontSee('Details on Instagram');
    }

    public function test_menu_item_is_internal_and_no_longer_points_to_education_md(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('href="'.route('opportunities').'"', false)
            ->assertDontSee('https://education.md/opportunities', false);
    }

    public function test_every_entry_has_its_photo_and_three_languages(): void
    {
        $data = json_decode((string) file_get_contents(resource_path('data/opportunities.json')), true);

        foreach ($data['items'] as $item) {
            $this->assertFileExists(public_path('img/opportunities/'.$item['image']), $item['code']);
            foreach (['ru', 'en', 'ro'] as $lang) {
                $this->assertNotEmpty($item['text'][$lang] ?? '', $item['code'].' '.$lang);
                $this->assertNotEmpty($item['country'][$lang] ?? '', $item['code'].' '.$lang);
            }
        }
    }

    public function test_cabinet_shows_the_same_opportunities_and_nobody_can_publish(): void
    {
        $u = \App\Models\BotUser::create(['telegram_id' => 777002, 'telegram_username' => 'opp_test', 'full_name' => 'Opp Test', 'status' => \App\Models\BotUser::STATUS_APPROVED]);
        $session = ['account_telegram_id' => $u->telegram_id, '_account_expires' => now()->addDay()->timestamp];

        $html = $this->withSession($session)->get(route('account.opportunities.index'))->assertOk()->getContent();
        $this->assertStringContainsString(route('opportunities.show', 'emotion-armenia'), $html);
        $this->assertStringContainsString('cab-opp', $html);
        $this->assertStringNotContainsString(route('account.index').'/opportunities/create', $html);

        $this->withSession($session)->get('/app/account/opportunities/create')->assertNotFound();
        $this->withSession($session)->post('/app/account/opportunities', ['type' => 'project', 'title' => 'x', 'body' => 'y'])->assertStatus(405);
    }
}
