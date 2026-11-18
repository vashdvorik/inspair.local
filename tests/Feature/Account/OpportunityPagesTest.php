<?php

declare(strict_types=1);

namespace Tests\Feature\Account;

use App\Models\BotUser;
use App\Support\OpportunityFeed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** «Возможности» в кабинете: список и полные страницы открываются внутри кабинета, а не на сайте. */
class OpportunityPagesTest extends TestCase
{
    use RefreshDatabase;

    private function sess(array $attrs = []): array
    {
        static $n = 910000;
        $n++;
        $u = BotUser::create($attrs + ['telegram_id' => $n, 'telegram_username' => 'u'.$n, 'full_name' => 'Test '.$n, 'status' => BotUser::STATUS_APPROVED]);

        return ['account_telegram_id' => $u->telegram_id, '_account_expires' => now()->addDay()->timestamp];
    }

    public function test_list_links_stay_inside_the_cabinet(): void
    {
        $slug = OpportunityFeed::all()[0]['slug'];

        $html = $this->withSession($this->sess())->get(route('account.opportunities.index'))->assertOk()->getContent();

        $this->assertStringContainsString(route('account.opportunities.show', $slug), $html);
        $this->assertStringNotContainsString(route('opportunities.show', $slug), $html);
    }

    public function test_every_opportunity_opens_in_the_cabinet_with_its_full_text(): void
    {
        $session = $this->sess();

        foreach (OpportunityFeed::all() as $o) {
            $this->withSession($session)->get(route('account.opportunities.show', $o['slug']))
                ->assertOk()
                ->assertSee(e($o['name']), false)
                ->assertSee(route('account.opportunities.index'), false);
        }
    }

    public function test_unknown_opportunity_is_404_and_banned_user_still_sees_it(): void
    {
        $this->withSession($this->sess())->get(route('account.opportunities.show', 'no-such'))->assertNotFound();

        $slug = OpportunityFeed::all()[0]['slug'];
        $this->withSession($this->sess(['status' => BotUser::STATUS_BANNED]))->get(route('account.opportunities.show', $slug))->assertOk();
    }
}
