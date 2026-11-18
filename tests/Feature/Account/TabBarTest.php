<?php

declare(strict_types=1);

namespace Tests\Feature\Account;

use App\Models\BotUser;
use App\Services\GameService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Нижнее меню кабинета на телефоне: главные разделы под большим пальцем. */
class TabBarTest extends TestCase
{
    use RefreshDatabase;

    private function sess(): array
    {
        $u = BotUser::factory()->create();

        return ['account_telegram_id' => $u->telegram_id, '_account_expires' => now()->addDay()->timestamp];
    }

    private function tabbar(string $html): string
    {
        preg_match('#<nav class="cab-tabbar.*?</nav>#s', $html, $m);

        return $m[0] ?? '';
    }

    public function test_tab_bar_links_the_main_sections_and_marks_the_current_one(): void
    {
        $html = $this->withSession($this->sess())->get(route('account.opportunities.index'))->assertOk()->getContent();
        $bar = $this->tabbar($html);

        $this->assertNotSame('', $bar);
        foreach (['account.index', 'account.people', 'account.opportunities.index', 'account.profile'] as $route) {
            $this->assertStringContainsString('href="'.route($route).'"', $bar, $route);
        }
        $this->assertStringContainsString('is-active', $bar);
        $this->assertMatchesRegularExpression('#href="'.preg_quote(route('account.opportunities.index'), '#').'"[^>]*is-active#', $bar);
    }

    public function test_game_tab_appears_only_when_the_game_is_on(): void
    {
        $session = $this->sess();

        app(GameService::class)->setMode(GameService::MODE_OFF);
        $this->assertStringNotContainsString(route('account.game'), $this->tabbar($this->withSession($session)->get(route('account.index'))->getContent()));

        app(GameService::class)->setMode(GameService::MODE_LIVE);
        $this->assertStringContainsString(route('account.game'), $this->tabbar($this->withSession($session)->get(route('account.index'))->getContent()));
    }
}
