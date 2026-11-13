<?php

declare(strict_types=1);

namespace Tests\Feature\Game;

use App\Models\User;
use App\Services\GameService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GameAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app(GameService::class)->setMode(GameService::MODE_WAITING); // по умолчанию игра выключена: тестам нужна включённая
    }

    public function test_admin_pages_render_and_launch_toggle_works(): void
    {
        $admin = User::factory()->create();
        $this->actingAs($admin);

        $this->get('/admin/game-admin')->assertOk()->assertSee('Режим игры');
        $this->get('/admin/ai-settings')->assertOk()->assertSee('quest_generate');

        \Livewire\Livewire::test(\App\Filament\Pages\GameAdmin::class)->call('setMode', 'live');
        $this->assertTrue(app(GameService::class)->isLaunched());
    }
}