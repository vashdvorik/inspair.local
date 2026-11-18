<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Pages\AiSettings;
use App\Models\AppSetting;
use App\Models\User;
use App\Services\Ai\OpenRouter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

/** Ключ OpenRouter вводится в админке («ИИ»), хранится зашифрованным и главнее строки из .env. */
class AiApiKeyTest extends TestCase
{
    use RefreshDatabase;

    public function test_key_from_the_panel_is_stored_encrypted_and_wins_over_env(): void
    {
        config(['ai.openrouter.key' => 'sk-or-v1-from-env-0000000000']);
        $this->assertSame('env', OpenRouter::keySource());

        $this->actingAs(User::factory()->create());
        Livewire::test(AiSettings::class)
            ->set('newKey', 'sk-or-v1-from-admin-abcdef123456')
            ->call('saveKey')
            ->assertSet('newKey', '');   // поле очищается: ключ больше нигде не показывается

        $this->assertSame('admin', OpenRouter::keySource());
        $this->assertSame('sk-or-v1-from-admin-abcdef123456', OpenRouter::apiKey());
        $this->assertStringNotContainsString('from-admin', (string) DB::table('app_settings')->value('value'), 'в базе ключ лежит зашифрованным');
    }

    public function test_requests_use_the_panel_key(): void
    {
        config(['ai.openrouter.key' => null]);
        AppSetting::putSecret(OpenRouter::KEY_SETTING, 'sk-or-v1-from-admin-abcdef123456');
        Http::fake(['openrouter.ai/*' => Http::response(['choices' => [['message' => ['content' => 'работает']]]])]);

        $r = app(OpenRouter::class)->chat('quest_generate', [['role' => 'user', 'content' => 'привет']]);

        $this->assertSame('работает', $r['text']);
        Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'Bearer sk-or-v1-from-admin-abcdef123456'));
    }

    public function test_bad_input_is_rejected_and_removing_returns_to_env(): void
    {
        config(['ai.openrouter.key' => 'sk-or-v1-from-env-0000000000']);
        $this->actingAs(User::factory()->create());

        Livewire::test(AiSettings::class)->set('newKey', 'короткий ключ')->call('saveKey');
        $this->assertSame('env', OpenRouter::keySource(), 'мусор не сохраняется');

        AppSetting::putSecret(OpenRouter::KEY_SETTING, 'sk-or-v1-from-admin-abcdef123456');
        Livewire::test(AiSettings::class)->call('removeKey');
        $this->assertSame('env', OpenRouter::keySource());
        $this->assertSame('sk-or-v1-from-env-0000000000', OpenRouter::apiKey());
    }

    public function test_without_any_key_nothing_works_and_page_says_so(): void
    {
        config(['ai.openrouter.key' => null]);
        $this->actingAs(User::factory()->create());

        $this->assertNull(OpenRouter::keySource());
        $this->get('/admin/ai-settings')->assertOk()->assertSee('Ключ не задан');
    }
}
