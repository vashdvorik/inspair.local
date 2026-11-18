<?php

declare(strict_types=1);

namespace Tests\Feature\Account;

use App\Models\BotUser;
use App\Services\GameService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Предварительной модерации нет: любой зарегистрированный участник сразу пользуется кабинетом и игрой.
 * Администратор может выдать теневой бан: человек ничего не замечает, но другие его не видят.
 */
class ShadowBanTest extends TestCase
{
    use RefreshDatabase;

    private function sess(BotUser $u): array
    {
        return ['account_telegram_id' => $u->telegram_id, '_account_expires' => now()->addDay()->timestamp];
    }

    public function test_new_participant_has_every_section_right_away(): void
    {
        app(GameService::class)->setMode(GameService::MODE_LIVE);
        $u = BotUser::factory()->create(['level' => 2, 'xp' => 120]);

        $this->assertTrue($u->isApproved());
        $this->withSession($this->sess($u))->get(route('account.game'))->assertOk()->assertSee('game-app', false);
        $this->withSession($this->sess($u))->get(route('account.people'))->assertOk();
        $this->withSession($this->sess($u))->get(route('account.opportunities.index'))->assertOk();
        $this->withSession($this->sess($u))->getJson(route('account.game.api.state'))->assertOk();
    }

    public function test_banned_participant_notices_nothing(): void
    {
        app(GameService::class)->setMode(GameService::MODE_LIVE);
        $banned = BotUser::factory()->banned()->create(['level' => 2, 'xp' => 120]);
        $other = BotUser::factory()->approved()->create(['full_name' => 'Влад Мунтян']);

        $this->withSession($this->sess($banned))->get(route('account.game'))->assertOk()->assertSee('game-app', false);
        $this->withSession($this->sess($banned))->getJson(route('account.game.api.state'))->assertOk();
        $this->withSession($this->sess($banned))->get(route('account.opportunities.index'))->assertOk();
        $this->withSession($this->sess($banned))->get(route('account.index'))->assertOk();

        // видит других участников как обычный
        $this->withSession($this->sess($banned))->get(route('account.people'))->assertOk()->assertSee('Влад Мунтян', false);
        $this->withSession($this->sess($banned))->get(route('account.people.show', $other))->assertOk();
    }

    public function test_banned_participant_is_invisible_to_others(): void
    {
        $banned = BotUser::factory()->banned()->create(['full_name' => 'Скрытый Участник']);
        $viewer = BotUser::factory()->approved()->create();

        $this->withSession($this->sess($viewer))->get(route('account.people'))->assertOk()->assertDontSee('Скрытый Участник', false);
        $this->withSession($this->sess($viewer))->get(route('account.people.show', $banned))->assertNotFound();
        $this->assertFalse(BotUser::approved()->whereKey($banned->id)->exists());
        $this->assertTrue(BotUser::banned()->whereKey($banned->id)->exists());
    }
}
