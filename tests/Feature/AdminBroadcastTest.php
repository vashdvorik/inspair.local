<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BotUser;
use App\Models\Broadcast;
use App\Models\BroadcastDelivery;
use App\Services\BroadcastMessenger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Сообщения администратора участникам в бот: кому уходят, когда, без повторов. */
class AdminBroadcastTest extends TestCase
{
    use RefreshDatabase;

    /** @var list<array{int, int}> [chat_id, broadcast_id] */
    public array $sent = [];

    public ?int $failFor = null;

    protected function setUp(): void
    {
        parent::setUp();

        $test = $this;
        $this->app->instance(BroadcastMessenger::class, new class($test) extends BroadcastMessenger {
            public function __construct(private AdminBroadcastTest $t) {}

            public function send(int $chatId, Broadcast $b): void
            {
                if ($this->t->failFor === $chatId) {
                    throw new \RuntimeException('Forbidden: bot was blocked by the user');
                }
                $this->t->sent[] = [$chatId, $b->id];
            }
        });
    }

    private function make(array $attrs = []): Broadcast
    {
        return Broadcast::create($attrs + ['title' => 'Тест', 'body' => '<b>Привет</b>', 'status' => Broadcast::SCHEDULED]);
    }

    public function test_scheduled_broadcast_reaches_those_who_did_not_mute_it(): void
    {
        $on = BotUser::factory()->create();
        $banned = BotUser::factory()->banned()->create();
        $muted = BotUser::factory()->create(['notifications_muted' => true]);
        $b = $this->make();

        $this->artisan('broadcasts:send')->assertSuccessful();

        $ids = array_column($this->sent, 0);
        $this->assertContains((int) $on->telegram_id, $ids);
        $this->assertContains((int) $banned->telegram_id, $ids);
        $this->assertNotContains((int) $muted->telegram_id, $ids);

        $b->refresh();
        $this->assertSame(Broadcast::SENT, $b->status);
        $this->assertSame(2, $b->recipients_count);
        $this->assertNotNull($b->sent_at);
    }

    public function test_audience_all_ignores_muted_notifications(): void
    {
        $muted = BotUser::factory()->create(['notifications_muted' => true]);
        $this->make(['audience' => Broadcast::AUDIENCE_ALL]);

        $this->artisan('broadcasts:send')->assertSuccessful();

        $this->assertContains((int) $muted->telegram_id, array_column($this->sent, 0));
    }

    public function test_drafts_and_future_broadcasts_are_not_sent(): void
    {
        BotUser::factory()->create();
        $this->make(['status' => Broadcast::DRAFT]);
        $future = $this->make(['scheduled_at' => now()->addHour()]);

        $this->artisan('broadcasts:send')->assertSuccessful();

        $this->assertSame([], $this->sent);
        $this->assertSame(Broadcast::SCHEDULED, $future->fresh()->status);

        $this->travel(2)->hours();
        $this->artisan('broadcasts:send')->assertSuccessful();
        $this->assertCount(1, $this->sent);
    }

    public function test_everyone_gets_it_once_and_failures_are_recorded(): void
    {
        $a = BotUser::factory()->create();
        $blocked = BotUser::factory()->create();
        $this->failFor = (int) $blocked->telegram_id;
        $b = $this->make();

        $this->artisan('broadcasts:send')->assertSuccessful();
        $this->artisan('broadcasts:send')->assertSuccessful();   // уже отправлена: повторно ничего

        $this->assertCount(1, $this->sent);
        $b->refresh();
        $this->assertSame(1, $b->recipients_count);
        $this->assertSame(1, $b->failed_count);
        $this->assertStringContainsString('blocked', (string) BroadcastDelivery::where('telegram_id', $blocked->telegram_id)->value('error'));
    }

    public function test_interrupted_run_continues_without_sending_twice(): void
    {
        $first = BotUser::factory()->create();
        $second = BotUser::factory()->create();
        $b = $this->make(['status' => Broadcast::SENDING]);
        BroadcastDelivery::create(['broadcast_id' => $b->id, 'telegram_id' => $first->telegram_id, 'delivered' => true]);

        $this->artisan('broadcasts:send')->assertSuccessful();

        $this->assertSame([[(int) $second->telegram_id, $b->id]], $this->sent);
        $this->assertSame(2, $b->fresh()->recipients_count);
    }

    public function test_buttons_open_links_or_cabinet_pages(): void
    {
        config(['app.url' => 'https://inspire.education.md']);
        $b = $this->make(['buttons' => [
            ['label' => 'Читать', 'kind' => 'url', 'url' => 'https://example.com/news'],
            ['label' => 'Возможности', 'kind' => 'cabinet', 'page' => '/opportunities'],
            ['label' => '', 'kind' => 'url', 'url' => 'https://skip.me'],
        ]]);

        $this->assertSame([
            ['label' => 'Читать', 'url' => 'https://example.com/news', 'web_app' => false],
            ['label' => 'Возможности', 'url' => 'https://inspire.education.md/app/account/opportunities', 'web_app' => true],
        ], $b->keyboard());
    }

    public function test_admin_can_create_edit_and_queue_a_broadcast_in_the_panel(): void
    {
        $this->actingAs(\App\Models\User::factory()->create());

        // свои страницы админки используют утилиты Tailwind, их стили подключены отдельным файлом
        $this->get('/admin/broadcasts')->assertOk()->assertSee('Рассылки')->assertSee('design/admin-pages.css', false);
        $this->get('/admin/broadcasts/create')->assertOk()->assertSee('Текст сообщения');

        \Livewire\Livewire::test(\App\Filament\Resources\BroadcastResource\Pages\CreateBroadcast::class)
            ->fillForm([
                'title' => 'Новости недели',
                'body' => '<b>Привет!</b> Мы запустили хакатон.',
                'audience' => Broadcast::AUDIENCE_NOTIFY,
                'buttons' => [['label' => 'Подробнее', 'kind' => 'url', 'url' => 'https://inspire.education.md/events/hackathon-2026']],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $b = Broadcast::firstOrFail();
        $this->assertSame(Broadcast::DRAFT, $b->status);
        $this->assertSame('Подробнее', $b->buttons[0]['label']);

        $this->get("/admin/broadcasts/{$b->id}/edit")->assertOk();

        \Livewire\Livewire::test(\App\Filament\Resources\BroadcastResource\Pages\ListBroadcasts::class)
            ->callAction(\Filament\Actions\Testing\TestAction::make('send')->table($b))
            ->assertNotified();
        $this->assertSame(Broadcast::SCHEDULED, $b->fresh()->status);
    }
}
