<?php

declare(strict_types=1);

namespace Tests\Feature\Account;

use App\Models\BotUser;
use App\Models\ProfileFact;
use App\Services\GameService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Главная кабинета: не три плитки, а живая сводка (профиль, возможности, сообщество, игра). */
class HomeDashboardTest extends TestCase
{
    use RefreshDatabase;

    private function home(BotUser $u, string $lang = 'ru'): \Illuminate\Testing\TestResponse
    {
        return $this->withSession(['account_telegram_id' => $u->telegram_id, '_account_expires' => now()->addDay()->timestamp])
            ->withUnencryptedCookie(\App\Support\Lang::COOKIE, $lang)
            ->get(route('account.index'));
    }

    public function test_home_shows_opportunities_community_and_the_nearest_event(): void
    {
        $me = BotUser::factory()->create(['full_name' => 'Анна Кох']);
        BotUser::factory()->create(['full_name' => 'Новичок Иванов']);

        $this->home($me)->assertOk()
            ->assertDontSee('events-businesshub.lovable.app')
            ->assertSee('Новые возможности')
            ->assertSee('/app/account/opportunities/', false)
            ->assertSee('Новые участники')
            ->assertSee('Новичок');
    }

    public function test_profile_progress_shows_what_is_missing_and_goes_away_when_complete(): void
    {
        $me = BotUser::factory()->create(['description' => 'Коротко']);
        $this->home($me)->assertSee('Заполните профиль')->assertSee('0%')->assertSee('добавить фото')->assertSee('рассказать о себе')->assertDontSee('факта');

        $me->update(['description' => 'Достаточно длинное описание о себе для профиля']);
        $this->home($me->fresh())->assertSee('50%')->assertSee('добавить фото')->assertDontSee('рассказать о себе');

        $me->update(['avatar_path' => 'avatars/x.jpg']);
        $this->home($me->fresh())->assertDontSee('Заполните профиль');
    }

    public function test_game_card_only_when_the_game_is_on(): void
    {
        $me = BotUser::factory()->create(['xp' => 120, 'level' => 2]);

        app(GameService::class)->setMode(GameService::MODE_OFF);
        $this->home($me)->assertDontSee('home-game');

        app(GameService::class)->setMode(GameService::MODE_LIVE);
        $this->home($me)->assertSee('home-game')->assertSee('Активист');
    }

    public function test_home_is_translated_and_banned_participants_are_not_listed_to_others(): void
    {
        BotUser::factory()->banned()->create(['full_name' => 'Скрытый Человек']);
        BotUser::factory()->create(['full_name' => 'Видимая Анна']);
        $me = BotUser::factory()->create();

        $this->home($me, 'en')->assertOk()->assertSee('New opportunities')->assertSee('New members')->assertSee('Видимая')->assertDontSee('Скрытый');
    }
}
