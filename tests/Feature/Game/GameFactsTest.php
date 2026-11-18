<?php

declare(strict_types=1);

namespace Tests\Feature\Game;

use App\Models\BotUser;
use App\Models\ProfileFact;
use App\Services\GameService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Факты о себе пишутся прямо в игре (не только в профиле). */
class GameFactsTest extends TestCase
{
    use RefreshDatabase;

    private function sess(BotUser $u): array
    {
        return ['account_telegram_id' => $u->telegram_id, '_account_expires' => now()->addDay()->timestamp];
    }

    public function test_facts_are_saved_in_the_game_and_the_state_comes_back_updated(): void
    {
        app(GameService::class)->setMode(GameService::MODE_WAITING);
        $u = BotUser::factory()->create();

        $res = $this->withSession($this->sess($u))->postJson(route('account.game.api.facts'), [
            'new_facts' => ['Играю на гитаре', 'Люблю горы', 'Учу румынский'],
        ])->assertOk();

        $this->assertSame(3, $u->facts()->count());
        $res->assertJsonPath('facts.0.body', 'Играю на гитаре');
        $this->assertSame('facts', $res->json('checklist.2.key'));
        $this->assertTrue($res->json('checklist.2.done'), 'после трёх фактов пункт чек-листа выполнен');
        $this->assertGreaterThan(0, $u->fresh()->xp, 'за факты идут очки');
    }

    public function test_existing_facts_can_be_edited_or_deleted_and_the_limit_holds(): void
    {
        app(GameService::class)->setMode(GameService::MODE_LIVE);
        $u = BotUser::factory()->create();
        $keep = ProfileFact::create(['bot_user_id' => $u->id, 'body' => 'старый']);
        $drop = ProfileFact::create(['bot_user_id' => $u->id, 'body' => 'удалить']);

        $this->withSession($this->sess($u))->postJson(route('account.game.api.facts'), [
            'facts' => [$keep->id => 'новый текст', $drop->id => ''],
            'new_facts' => array_fill(0, 10, 'ещё один'),
        ])->assertOk();

        $this->assertSame('новый текст', $keep->fresh()->body);
        $this->assertNull(ProfileFact::find($drop->id));
        $this->assertSame((int) config('game.max_facts'), $u->facts()->count(), 'больше лимита не сохраняется');
    }

    public function test_only_own_facts_can_be_touched_and_input_is_validated(): void
    {
        app(GameService::class)->setMode(GameService::MODE_LIVE);
        $me = BotUser::factory()->create();
        $other = BotUser::factory()->create();
        $theirs = ProfileFact::create(['bot_user_id' => $other->id, 'body' => 'чужой факт']);

        $this->withSession($this->sess($me))->postJson(route('account.game.api.facts'), ['facts' => [$theirs->id => 'взлом']])->assertOk();
        $this->assertSame('чужой факт', $theirs->fresh()->body);

        $this->withSession($this->sess($me))->postJson(route('account.game.api.facts'), ['new_facts' => [str_repeat('я', 301)]])->assertStatus(422);
    }

    public function test_facts_endpoint_is_closed_while_the_game_is_off(): void
    {
        app(GameService::class)->setMode(GameService::MODE_OFF);

        $this->withSession($this->sess(BotUser::factory()->create()))->postJson(route('account.game.api.facts'), ['new_facts' => ['x']])->assertNotFound();
    }
}
