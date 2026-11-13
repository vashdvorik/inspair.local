<?php

declare(strict_types=1);

namespace Tests\Feature\Account;

use App\Models\BotUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** AI-матчи и AI-поиск можно выключить: разделы пропадают из меню, а адреса дают 404. */
class CabinetFeaturesTest extends TestCase
{
    use RefreshDatabase;

    private function sess(): array
    {
        $u = BotUser::factory()->approved()->create();

        return ['account_telegram_id' => $u->telegram_id, '_account_expires' => now()->addDay()->timestamp];
    }

    public function test_disabled_sections_are_gone(): void
    {
        config(['features.cabinet_matches' => false, 'features.cabinet_search' => false]);
        $s = $this->sess();

        $this->withSession($s)->get(route('account.matches'))->assertNotFound();
        $this->withSession($s)->get(route('account.search'))->assertNotFound();

        $html = $this->withSession($s)->get(route('account.index'))->assertOk()->getContent();
        $this->assertStringNotContainsString('AI-матчи', $html);
        $this->assertStringNotContainsString('AI-поиск', $html);
    }

    public function test_enabled_sections_are_in_the_menu(): void
    {
        $html = $this->withSession($this->sess())->get(route('account.index'))->assertOk()->getContent();
        $this->assertStringContainsString('AI-матчи', $html);
        $this->assertStringContainsString('AI-поиск', $html);
    }

    public function test_sections_are_off_by_default(): void
    {
        $this->assertFalse((bool) (require config_path('features.php'))['cabinet_matches']);
        $this->assertFalse((bool) (require config_path('features.php'))['cabinet_search']);
    }
}