<?php

declare(strict_types=1);

namespace Tests\Feature\Game;

use App\Jobs\GenerateQuestsJob;
use App\Models\User;
use App\Services\GameService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Tests\TestCase;

class LangSessionAndGenerateTest extends TestCase
{
    use RefreshDatabase;

    public function test_language_choice_wins_over_a_stale_cookie_via_session(): void
    {
        $this->withUnencryptedCookie('inspair_lang', 'ro')->get('/lang/en?to=/app/login')->assertRedirect('/app/login');

        $this->withUnencryptedCookie('inspair_lang', 'ro')->get('/app/login')->assertSee('lang="en"', false);
    }

    public function test_generate_button_queues_one_job_and_ignores_double_clicks(): void
    {
        app(GameService::class)->setMode(GameService::MODE_LIVE);
        Queue::fake();
        Cache::forget(GenerateQuestsJob::FLAG);
        $this->actingAs(User::factory()->create());

        Livewire::test(\App\Filament\Pages\GameAdmin::class)->call('generate')->call('generate')->call('generate');

        Queue::assertPushed(GenerateQuestsJob::class, 1);
    }

    public function test_stuck_generation_can_be_reset(): void
    {
        app(GameService::class)->setMode(GameService::MODE_LIVE);
        Cache::put(GenerateQuestsJob::FLAG, now()->subMinutes(5)->timestamp, now()->addMinutes(5));
        \Illuminate\Support\Facades\DB::table('jobs')->insert(['queue' => 'default', 'payload' => '{"displayName":"App\\Jobs\\GenerateQuestsJob"}', 'attempts' => 0, 'available_at' => time(), 'created_at' => time()]);
        $this->actingAs(User::factory()->create());

        Livewire::test(\App\Filament\Pages\GameAdmin::class)->assertSee('Сбросить')->call('cancelGeneration');

        $this->assertFalse(Cache::has(GenerateQuestsJob::FLAG));
        $this->assertSame(0, \Illuminate\Support\Facades\DB::table('jobs')->count());
    }
}
