<?php

declare(strict_types=1);

namespace Tests\Feature\Game;

use App\Models\BotUser;
use App\Models\Connection;
use App\Models\Quest;
use App\Models\User;
use App\Models\XpEvent;
use App\Services\GameReminders;
use App\Services\GameService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GameAdminAndRemindersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app(GameService::class)->setMode(GameService::MODE_WAITING); // по умолчанию игра выключена: тестам нужна включённая
    }

    private function player(array $attrs = []): BotUser
    {
        return BotUser::factory()->approved()->create($attrs + ['description' => 'Люблю дебаты и архивы, учусь в Комрате.']);
    }

    private function quest(BotUser $subject, array $others): Quest
    {
        return Quest::create([
            'subject_user_id' => $subject->id,
            'option_ids'      => collect($others)->pluck('id')->push($subject->id)->all(),
            'question'        => ['ru' => 'Кто любит дебаты?', 'en' => 'q', 'ro' => 'q'],
            'explanation'     => ['ru' => 'Он писал про дебаты.', 'en' => 'e', 'ro' => 'e'],
            'status'          => Quest::ACTIVE,
        ]);
    }

    public function test_no_reminders_before_launch(): void
    {
        Http::fake();
        $this->player();
        $this->assertSame(0, app(GameReminders::class)->run());
        Http::assertNothingSent();
    }

    public function test_recognized_reminder_comes_first_and_is_sent_once(): void
    {
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true])]);
        app(GameService::class)->setLaunched(true);
        $a = $this->player(['full_name' => 'Анна Чебан']);
        $b = $this->player(['full_name' => 'Влад Мунтян']);
        Connection::create(['user_id' => $a->id, 'target_id' => $b->id, 'source' => 'quest']);

        $this->assertSame(2, app(GameReminders::class)->run() + 1); // b получил напоминание (a: нет повода)
        Http::assertSent(fn ($r) => str_contains($r['text'], 'Анна Чебан') && str_contains($r['text'], 'узнал'));
        $this->assertNotNull(Connection::first()->target_notified_at);

        // сразу повторно ничего не уходит: прошло меньше 20 часов
        $this->assertSame(0, app(GameReminders::class)->run());
    }

    public function test_new_quest_reminder_only_for_idle_users_with_unseen_quests(): void
    {
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true])]);
        app(GameService::class)->setLaunched(true);
        $me = $this->player(['last_active_at' => now()->subDays(2)]);
        $busy = $this->player(['last_active_at' => now()]);
        $s = $this->player();
        $this->quest($s, [$this->player(), $this->player()]);

        $this->assertNotNull(app(GameReminders::class)->messageFor($me));
        $this->assertNull(app(GameReminders::class)->messageFor($busy)); // сегодня уже заходил
    }

    public function test_muted_users_and_level_up_of_friend(): void
    {
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true])]);
        app(GameService::class)->setLaunched(true);
        $me = $this->player(['notifications_muted' => true]);
        $friend = $this->player(['full_name' => 'Илона Руссу']);
        Connection::create(['user_id' => $me->id, 'target_id' => $friend->id, 'source' => 'quest']);
        XpEvent::create(['bot_user_id' => $friend->id, 'type' => 'level_up', 'ref' => '3', 'points' => 0]);

        $this->assertSame(0, \App\Models\BotUser::where('id', $me->id)->where('notifications_muted', false)->count());
        $msg = app(GameReminders::class)->messageFor($me->fresh()->forceFill(['notifications_muted' => false]));
        $this->assertStringContainsString('Илона Руссу', $msg['text']);
        $this->assertStringContainsString('Союзник', $msg['text']);
    }

    public function test_blocked_bot_mutes_user(): void
    {
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => false], 403)]);
        $u = $this->player();
        $this->assertFalse(app(GameReminders::class)->send($u, ['text' => 'x', 'after' => null]));
        $this->assertTrue($u->fresh()->notifications_muted);
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
