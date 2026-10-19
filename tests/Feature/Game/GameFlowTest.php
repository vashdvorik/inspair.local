<?php

declare(strict_types=1);

namespace Tests\Feature\Game;

use App\Models\BotUser;
use App\Models\Connection;
use App\Models\ProfileFact;
use App\Models\Quest;
use App\Models\XpEvent;
use App\Services\GameService;
use App\Services\QuestService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GameFlowTest extends TestCase
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

    private function sess(BotUser $u): array
    {
        return ['account_telegram_id' => $u->telegram_id, '_account_expires' => now()->addDay()->timestamp];
    }

    private function quest(BotUser $subject, array $others): Quest
    {
        return Quest::create([
            'subject_user_id' => $subject->id,
            'option_ids'      => collect($others)->pluck('id')->push($subject->id)->shuffle()->values()->all(),
            'question'        => ['ru' => 'Кто любит дебаты?', 'en' => 'Who loves debate?', 'ro' => 'Cine iubește dezbaterile?'],
            'explanation'     => ['ru' => 'Он писал про дебаты.', 'en' => 'He wrote about debate.', 'ro' => 'A scris despre dezbateri.'],
            'status'          => Quest::ACTIVE,
        ]);
    }

    // ───────────── очки и уровни ─────────────

    public function test_levels_follow_thresholds(): void
    {
        $g = app(GameService::class);
        $this->assertSame(1, $g->levelForXp(0));
        $this->assertSame(1, $g->levelForXp(99));
        $this->assertSame(2, $g->levelForXp(100));
        $this->assertSame(5, $g->levelForXp(1500));
    }

    public function test_award_is_idempotent_and_reports_level_up(): void
    {
        $g = app(GameService::class);
        $u = $this->player();

        $this->assertNull($g->award($u, 'profile_avatar', 30));
        $this->assertSame(2, $g->award($u, 'profile_description', 80, 'x'));
        $this->assertNull($g->award($u, 'profile_description', 80, 'x')); // повтор игнорируется

        $u->refresh();
        $this->assertSame(110, $u->xp);
        $this->assertSame(2, $u->level);
        $this->assertSame(1, XpEvent::where('type', 'level_up')->count());
    }

    public function test_profile_rewards_give_quick_access(): void
    {
        $u = $this->player(['avatar_path' => 'a.jpg', 'expectation' => 'Ищу команду для проектов о мире.', 'region' => 'comrat']);
        ProfileFact::create(['bot_user_id' => $u->id, 'body' => 'перевожу субтитры']);

        app(GameService::class)->syncProfileRewards($u);
        app(GameService::class)->syncProfileRewards($u); // повтор не задваивает

        $u->refresh();
        $this->assertSame(30 + 50 + 30 + 10 + 15, $u->xp);
        $this->assertSame(2, $u->level);
    }

    // ───────────── доступ ─────────────

    public function test_sections_closed_before_launch_and_for_low_levels_after(): void
    {
        $u = $this->player();

        $this->withSession($this->sess($u))->get(route('account.people'))->assertRedirect(route('account.game', ['locked' => 'people']));
        $this->withSession($this->sess($u))->get(route('account.search'))->assertRedirect(route('account.game', ['locked' => 'search']));

        app(GameService::class)->setLaunched(true);
        $this->withSession($this->sess($u))->get(route('account.people'))->assertRedirect(); // уровень 1 < 2

        $u->update(['xp' => 120, 'level' => 2]);
        $this->withSession($this->sess($u))->get(route('account.people'))->assertOk();
        $this->withSession($this->sess($u))->get(route('account.search'))->assertRedirect(); // нужен уровень 3

        $u->update(['xp' => 320, 'level' => 3]);
        $this->withSession($this->sess($u))->get(route('account.search'))->assertOk();
    }

    public function test_moderator_sees_everything_without_launch(): void
    {
        $m = $this->player(['is_moderator' => true]);
        $this->withSession($this->sess($m))->get(route('account.people'))->assertOk();
    }

    public function test_person_page_hides_fields_above_viewer_level(): void
    {
        app(GameService::class)->setLaunched(true);
        $viewer = $this->player(['xp' => 120, 'level' => 2]);
        $target = $this->player(['description' => 'СЕКРЕТНОЕ ОПИСАНИЕ участника', 'expectation' => 'СЕКРЕТНЫЕ ОЖИДАНИЯ участника']);

        $this->withSession($this->sess($viewer))->get(route('account.people.show', $target))
            ->assertOk()->assertDontSee('СЕКРЕТНОЕ ОПИСАНИЕ')->assertDontSee('СЕКРЕТНЫЕ ОЖИДАНИЯ');

        $viewer->update(['xp' => 760, 'level' => 4]);
        $this->withSession($this->sess($viewer))->get(route('account.people.show', $target))
            ->assertOk()->assertSee('СЕКРЕТНОЕ ОПИСАНИЕ')->assertSee('СЕКРЕТНЫЕ ОЖИДАНИЯ');
    }

    public function test_closed_contacts_hide_write_button(): void
    {
        $m = $this->player(['is_moderator' => true]);
        $open = $this->player(['telegram_username' => 'open_user']);
        $closed = $this->player(['telegram_username' => 'closed_user', 'contacts_closed' => true]);

        $this->withSession($this->sess($m))->get(route('account.people.show', $open))->assertSee('t.me/open_user', false);
        $this->withSession($this->sess($m))->get(route('account.people.show', $closed))->assertDontSee('t.me/closed_user', false);
    }

    // ───────────── квесты ─────────────

    public function test_correct_answer_adds_to_network_and_awards_xp(): void
    {
        app(GameService::class)->setLaunched(true);
        $me = $this->player();
        $subject = $this->player();
        $quest = $this->quest($subject, [$this->player(), $this->player()]);

        $this->withSession($this->sess($me))->postJson(route('account.game.api.answer', $quest), ['chosen' => $subject->id])
            ->assertOk()->assertJsonPath('correct', true)->assertJsonPath('gained', 20)->assertJsonPath('connected', true);

        $this->assertTrue(Connection::where('user_id', $me->id)->where('target_id', $subject->id)->exists());
        $this->assertSame(20, $me->fresh()->xp);
    }

    public function test_wrong_answer_gives_small_xp_cooldown_and_no_connection(): void
    {
        app(GameService::class)->setLaunched(true);
        $me = $this->player();
        $subject = $this->player();
        $wrong = $this->player();
        $quest = $this->quest($subject, [$wrong, $this->player()]);

        $this->withSession($this->sess($me))->postJson(route('account.game.api.answer', $quest), ['chosen' => $wrong->id])
            ->assertOk()->assertJsonPath('correct', false)->assertJsonPath('gained', 3)->assertJsonPath('wait', 8)
            ->assertJsonPath('subject.id', $subject->id);

        $this->assertSame(0, Connection::count());

        // сразу после промаха следующий квест недоступен
        $this->withSession($this->sess($me))->getJson(route('account.game.api.quest'))->assertJsonPath('status', 'wait');
    }

    public function test_cannot_answer_twice_or_pick_foreign_option(): void
    {
        app(GameService::class)->setLaunched(true);
        $me = $this->player();
        $subject = $this->player();
        $quest = $this->quest($subject, [$this->player(), $this->player()]);
        $stranger = $this->player();

        $this->withSession($this->sess($me))->postJson(route('account.game.api.answer', $quest), ['chosen' => $stranger->id])->assertStatus(422);
        $this->withSession($this->sess($me))->postJson(route('account.game.api.answer', $quest), ['chosen' => $subject->id])->assertOk();
        $this->withSession($this->sess($me))->postJson(route('account.game.api.answer', $quest), ['chosen' => $subject->id])->assertStatus(409);
    }

    public function test_quest_endpoint_never_leaks_correct_answer(): void
    {
        app(GameService::class)->setLaunched(true);
        $me = $this->player();
        $subject = $this->player();
        $this->quest($subject, [$this->player(), $this->player()]);

        $res = $this->withSession($this->sess($me))->getJson(route('account.game.api.quest'))->assertOk()->assertJsonPath('status', 'ok');
        $this->assertStringNotContainsString('correct', json_encode($res->json()));
        $this->assertCount(3, $res->json('options'));
        $this->assertArrayNotHasKey('description', $res->json('options.0'));
    }

    public function test_quest_not_available_before_launch(): void
    {
        $me = $this->player();
        $this->withSession($this->sess($me))->getJson(route('account.game.api.quest'))->assertJsonPath('status', 'not_launched');
    }

    public function test_mutual_guess_makes_golden_thread_and_pays_both(): void
    {
        app(GameService::class)->setLaunched(true);
        $a = $this->player();
        $b = $this->player();
        $c = $this->player();

        $qa = $this->quest($b, [$c, $this->player()]);
        $qb = $this->quest($a, [$c, $this->player()]);

        $this->withSession($this->sess($a))->postJson(route('account.game.api.answer', $qa), ['chosen' => $b->id])->assertOk();
        $this->withSession($this->sess($b))->postJson(route('account.game.api.answer', $qb), ['chosen' => $a->id])
            ->assertOk()->assertJsonPath('mutual', true);

        $this->assertSame(2, Connection::where('mutual', true)->count());
        $this->assertSame(20 + 25, $a->fresh()->xp);
        $this->assertSame(20 + 25, $b->fresh()->xp);
    }

    public function test_reciprocate_requires_incoming_connection(): void
    {
        app(GameService::class)->setLaunched(true);
        $a = $this->player();
        $b = $this->player();

        $this->withSession($this->sess($b))->postJson(route('account.game.api.reciprocate', $a))->assertStatus(422);

        Connection::create(['user_id' => $a->id, 'target_id' => $b->id, 'source' => 'quest']);
        $this->withSession($this->sess($b))->postJson(route('account.game.api.reciprocate', $a))->assertOk();
        $this->assertSame(2, Connection::where('mutual', true)->count());
    }

    public function test_reports_hide_quest(): void
    {
        app(GameService::class)->setLaunched(true);
        $subject = $this->player();
        $quest = $this->quest($subject, [$this->player(), $this->player()]);

        foreach ([$this->player(), $this->player()] as $reporter) {
            $this->withSession($this->sess($reporter))->postJson(route('account.game.api.report', $quest), ['reason' => 'странный'])->assertOk();
        }
        $this->assertSame(Quest::REPORTED, $quest->fresh()->status);
    }

    // ───────────── генерация ─────────────

    public function test_generation_creates_verified_quest_and_rejects_ambiguous_one(): void
    {
        config(['ai.openrouter.key' => 'test']);
        $a = $this->player(['description' => 'Мечтаю открыть школу робототехники в своём городе.']);
        $b = $this->player(['description' => 'Люблю читать книги про историю и ходить в походы.']);
        $c = $this->player(['description' => 'Занимаюсь музыкой и пишу песни для друзей каждую неделю.']);
        ProfileFact::create(['bot_user_id' => $a->id, 'body' => 'хочу научить детей делать роботов']);

        $draft = ['question' => ['ru' => 'Кто мечтает научить детей робототехнике?', 'en' => 'Who dreams of teaching kids robotics?', 'ro' => 'Cine visează să predea robotică?'],
                  'explanation' => ['ru' => 'Он писал про школу.', 'en' => 'He wrote about a school.', 'ro' => 'A scris despre o școală.']];

        // верификатор отвечает «подходит только A»: метки перемешаны, поэтому подставляем ответ по содержимому запроса
        Http::fake(function ($request) use ($draft) {
            $body = json_decode($request->body(), true);
            $text = $body['messages'][1]['content'] ?? '';
            if (str_contains($text, 'Вопрос:')) {
                preg_match_all('/^([ABC]):\n(.*?)(?=\n\n[ABC]:|\z)/ms', $text, $m, PREG_SET_ORDER);
                $label = 'A';
                foreach ($m as $row) {
                    if (str_contains($row[2], 'робототехники') || str_contains($row[2], 'роботов')) {
                        $label = $row[1];
                    }
                }

                return Http::response(['choices' => [['message' => ['content' => json_encode(['matches' => [$label], 'safe' => true])]]]]);
            }

            return Http::response(['choices' => [['message' => ['content' => json_encode($draft)]]]]);
        });

        // у b и c уже есть квесты, поэтому субъектом станет a (меньше всего квестов)
        $this->quest($b, [$a, $c]);
        $this->quest($c, [$a, $b]);

        $created = app(QuestService::class)->generate(1);
        $this->assertSame(1, $created);
        $q = Quest::where('subject_user_id', $a->id)->first();
        $this->assertSame($a->id, $q->subject_user_id);
        $this->assertCount(3, $q->option_ids);
        $this->assertContains($b->id, $q->option_ids);
    }

    public function test_generation_skips_when_verifier_says_ambiguous(): void
    {
        config(['ai.openrouter.key' => 'test']);
        $this->player(); $this->player(); $this->player();

        Http::fake(function ($request) {
            $text = json_decode($request->body(), true)['messages'][1]['content'] ?? '';
            if (str_contains($text, 'Вопрос:')) {
                return Http::response(['choices' => [['message' => ['content' => json_encode(['matches' => ['A', 'B'], 'safe' => true])]]]]);
            }

            return Http::response(['choices' => [['message' => ['content' => json_encode([
                'question' => ['ru' => 'Кто любит дебаты?', 'en' => 'x', 'ro' => 'y'], 'explanation' => ['ru' => 'z', 'en' => 'z', 'ro' => 'z'],
            ])]]]]);
        });

        $this->assertSame(0, app(QuestService::class)->generate(1));
        $this->assertSame(0, Quest::count());
    }

    public function test_opt_out_users_are_not_used(): void
    {
        $this->player(); $this->player();
        $this->player(['quests_opt_out' => true]);

        $this->assertSame(2, app(QuestService::class)->eligibleUsers()->count());
        $this->assertSame(0, app(QuestService::class)->generate(3)); // меньше трёх доступных
    }

    // ───────────── профиль ─────────────

    public function test_profile_update_saves_facts_privacy_and_pays_xp(): void
    {
        $u = $this->player();
        $this->withSession($this->sess($u))->post(route('account.profile.update'), [
            'full_name'       => 'Мария Петреску',
            'description'     => 'Люблю дебаты и архивы, учусь в Комрате.',
            'new_facts'       => ['перевожу субтитры', '', 'играю на гитаре'],
            'contacts_closed' => '1',
            'quests_opt_out'  => '1',
        ])->assertRedirect(route('account.profile'));

        $u->refresh();
        $this->assertTrue($u->contacts_closed);
        $this->assertTrue($u->quests_opt_out);
        $this->assertSame(2, $u->facts()->count());
        $this->assertSame(50 + 15 + 15, $u->xp);

        // очистили поле факта: факт удалён
        $fact = $u->facts()->first();
        $this->withSession($this->sess($u))->post(route('account.profile.update'), [
            'full_name' => 'Мария Петреску', 'facts' => [$fact->id => ''],
        ])->assertRedirect();
        $this->assertSame(1, $u->facts()->count());
        $this->assertFalse($u->fresh()->contacts_closed);
    }

    public function test_game_state_endpoint_for_waiting_player(): void
    {
        $u = $this->player();
        $this->withSession($this->sess($u))->getJson(route('account.game.api.state'))
            ->assertOk()->assertJsonPath('launched', false)->assertJsonStructure(['me' => ['level', 'xp', 'percent'], 'levels', 'checklist', 'waiting' => ['ready', 'target']]);
        $this->withSession($this->sess($u))->get(route('account.game'))->assertOk()->assertSee('game-app', false);
    }
}
