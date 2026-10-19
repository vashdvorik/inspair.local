<?php

declare(strict_types=1);

namespace Tests\Feature\Game;

use App\Models\BotUser;
use App\Models\Connection;
use App\Models\ProfileFact;
use App\Models\User;
use App\Services\GameReminders;
use App\Services\GameService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/** Выключенная игра исчезает без следа: кабинет работает как до неё. */
class GameOffSwitchTest extends TestCase
{
    use RefreshDatabase;

    private function player(array $attrs = []): BotUser
    {
        return BotUser::factory()->approved()->create($attrs + ['description' => 'Люблю дебаты и архивы, учусь в Комрате.']);
    }

    private function sess(BotUser $u): array
    {
        return ['account_telegram_id' => $u->telegram_id, '_account_expires' => now()->addDay()->timestamp];
    }

    public function test_default_mode_is_off(): void
    {
        $this->assertSame('off', app(GameService::class)->mode());
        $this->assertFalse(app(GameService::class)->isEnabled());
    }

    public function test_game_pages_and_api_do_not_exist_when_off(): void
    {
        $u = $this->player();
        $this->withSession($this->sess($u))->get(route('account.game'))->assertNotFound();
        $this->withSession($this->sess($u))->getJson(route('account.game.api.state'))->assertNotFound();
        $this->withSession($this->sess($u))->getJson(route('account.game.api.quest'))->assertNotFound();
        $this->withSession($this->sess($u))->getJson(route('account.game.api.network'))->assertNotFound();
    }

    public function test_cabinet_is_open_to_everyone_like_before_the_game(): void
    {
        $low = $this->player(); // уровень 1
        $other = $this->player(['description' => 'ПОЛНОЕ ОПИСАНИЕ участника', 'expectation' => 'ПОЛНЫЕ ОЖИДАНИЯ участника']);

        foreach (['account.people', 'account.search', 'account.matches', 'account.opportunities.create'] as $route) {
            $this->withSession($this->sess($low))->get(route($route))->assertOk();
        }
        $this->withSession($this->sess($low))->get(route('account.people.show', $other))
            ->assertOk()->assertSee('ПОЛНОЕ ОПИСАНИЕ')->assertSee('ПОЛНЫЕ ОЖИДАНИЯ');
    }

    public function test_no_trace_of_game_in_menu_and_profile_form(): void
    {
        $u = $this->player();
        $this->withSession($this->sess($u))->get(route('account.index'))->assertOk()->assertDontSee('account/game', false);
        $this->withSession($this->sess($u))->get(route('account.profile.edit'))->assertOk()
            ->assertDontSee('Факты о себе')->assertDontSee('Не использовать меня в квестах')->assertDontSee('Тише: без напоминаний')
            ->assertSee('Закрыть мои контакты'); // приватность остаётся
    }

    public function test_no_points_and_no_reminders_when_off(): void
    {
        Http::fake();
        $u = $this->player(['avatar_path' => 'a.jpg']);
        app(GameService::class)->syncProfileRewards($u);
        $this->assertSame(0, $u->fresh()->xp);
        $this->assertSame(0, app(GameReminders::class)->run());
        Http::assertNothingSent();
    }

    public function test_saving_profile_keeps_game_flags_when_off(): void
    {
        $u = $this->player(['quests_opt_out' => true, 'notifications_muted' => true]);
        $this->withSession($this->sess($u))->post(route('account.profile.update'), ['full_name' => 'Мария Петреску'])->assertRedirect();
        $u->refresh();
        $this->assertTrue($u->quests_opt_out);
        $this->assertTrue($u->notifications_muted);
        $this->assertSame(0, $u->facts()->count());
    }

    public function test_closed_contacts_still_work_when_off(): void
    {
        $viewer = $this->player();
        $closed = $this->player(['telegram_username' => 'closed_user', 'contacts_closed' => true]);
        $this->withSession($this->sess($viewer))->get(route('account.people.show', $closed))->assertDontSee('t.me/closed_user', false);
    }

    public function test_data_survives_off_and_returns_when_enabled_again(): void
    {
        $g = app(GameService::class);
        $g->setMode('live');
        $u = $this->player();
        $g->award($u, 'quest_correct', 120, '1');
        $this->assertSame(2, $u->fresh()->level);

        $g->setMode('off');
        $this->assertSame(120, $u->fresh()->xp);        // данные на месте
        $this->assertNull($g->award($u, 'x', 50, '2'));  // но очки не растут

        $g->setMode('live');
        $this->assertSame(120, $u->fresh()->xp);
        $this->assertSame(2, $u->fresh()->level);
    }

    public function test_bot_link_with_game_target_falls_back_to_dashboard_when_off(): void
    {
        $u = $this->player();
        $token = \App\Models\LoginToken::generateFor((int) $u->telegram_id);
        $this->get(route('account.auth', ['token' => $token->token, 'to' => 'game']))->assertRedirect(route('account.index'));

        app(GameService::class)->setMode('waiting');
        $this->get(route('account.auth', ['token' => $token->token, 'to' => 'game']))->assertRedirect(route('account.game'));
    }

    public function test_admin_can_switch_modes_and_wipe_data(): void
    {
        $this->actingAs(User::factory()->create());
        $g = app(GameService::class);

        \Livewire\Livewire::test(\App\Filament\Pages\GameAdmin::class)->call('setMode', 'waiting');
        $this->assertSame('waiting', $g->mode());

        $u = $this->player();
        ProfileFact::create(['bot_user_id' => $u->id, 'body' => 'факт']);
        Connection::create(['user_id' => $u->id, 'target_id' => $this->player()->id, 'source' => 'quest']);
        $g->award($u, 'quest_correct', 50, '9');

        // в запущенной игре стирать нельзя
        $g->setMode('live');
        \Livewire\Livewire::test(\App\Filament\Pages\GameAdmin::class)->call('wipe');
        $this->assertSame(1, Connection::count());

        \Livewire\Livewire::test(\App\Filament\Pages\GameAdmin::class)->call('setMode', 'off')->call('wipe');
        $this->assertSame(0, Connection::count());
        $this->assertSame(0, ProfileFact::count());
        $this->assertSame(0, $u->fresh()->xp);

        $this->get('/admin/game-admin')->assertOk(); // админ видит страницу и в выключенном режиме
    }
}
