<?php

declare(strict_types=1);

namespace Tests\Feature\Account;

use App\Models\BotUser;
use App\Services\GameService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** В кабинет заходит любой зарегистрированный, а игра, участники и возможности ждут решения модератора. */
class ModerationGateTest extends TestCase
{
    use RefreshDatabase;

    private function sess(BotUser $u): array
    {
        return ['account_telegram_id' => $u->telegram_id, '_account_expires' => now()->addDay()->timestamp];
    }

    public function test_pending_participant_sees_waiting_notice(): void
    {
        app(GameService::class)->setMode(GameService::MODE_LIVE);
        $u = BotUser::factory()->pending()->create();
        $other = BotUser::factory()->approved()->create(['full_name' => 'Влад Мунтян']);

        foreach ([route('account.game'), route('account.people'), route('account.people.show', $other), route('account.opportunities.index')] as $url) {
            $html = $this->withSession($this->sess($u))->get($url)->assertOk()->getContent();
            $this->assertStringContainsString('Модератор ещё не посмотрел ваш профиль', $html, $url);
            $this->assertStringNotContainsString('Влад Мунтян', preg_replace('#<aside.*?</aside>#s', '', $html), $url);
        }

        // сам кабинет и профиль открыты
        $this->withSession($this->sess($u))->get(route('account.index'))->assertOk();
        $this->withSession($this->sess($u))->get(route('account.profile.edit'))->assertOk();
    }

    public function test_rejected_participant_sees_refusal_and_moderator_message(): void
    {
        app(GameService::class)->setMode(GameService::MODE_LIVE);
        $u = BotUser::factory()->create([
            'status'          => BotUser::STATUS_REJECTED,
            'moderation_note' => "Профиль пустой.\nДобавьте информацию о себе <b>и напишите нам</b>.",
        ]);

        foreach ([route('account.game'), route('account.people')] as $url) {
            $html = $this->withSession($this->sess($u))->get($url)->assertOk()->getContent();
            $this->assertStringContainsString('Модератор отказал вам в доступе к игре', $html, $url);
            $this->assertStringContainsString('Профиль пустой.', $html);
            $this->assertStringContainsString('&lt;b&gt;и напишите нам&lt;/b&gt;', $html); // сообщение экранируется
        }
    }

    public function test_rejected_without_message_shows_only_the_refusal(): void
    {
        app(GameService::class)->setMode(GameService::MODE_LIVE);
        $u = BotUser::factory()->create(['status' => BotUser::STATUS_REJECTED]);

        $html = $this->withSession($this->sess($u))->get(route('account.people'))->assertOk()->getContent();
        $this->assertStringContainsString('Модератор отказал вам в доступе к игре', $html);
        $this->assertStringNotContainsString('Сообщение модератора', $html);
    }

    public function test_game_api_is_closed_for_unapproved(): void
    {
        app(GameService::class)->setMode(GameService::MODE_LIVE);
        $u = BotUser::factory()->pending()->create();

        $this->withSession($this->sess($u))->getJson(route('account.game.api.state'))->assertForbidden();
        $this->withSession($this->sess($u))->getJson(route('account.game.api.quest'))->assertForbidden();
    }

    public function test_approved_participant_gets_through(): void
    {
        app(GameService::class)->setMode(GameService::MODE_LIVE);
        $u = BotUser::factory()->approved()->create(['level' => 2, 'xp' => 120]);

        $this->withSession($this->sess($u))->get(route('account.game'))->assertOk()->assertSee('game-app', false);
        $this->withSession($this->sess($u))->get(route('account.people'))->assertOk()->assertDontSee('Модератор ещё не посмотрел');
    }
}
