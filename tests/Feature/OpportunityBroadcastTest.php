<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BotUser;
use App\Services\OpportunityMessenger;
use App\Support\OpportunityFeed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** Новые возможности уходят участникам в бот ровно один раз; уведомления можно отключить в профиле. */
class OpportunityBroadcastTest extends TestCase
{
    use RefreshDatabase;

    /** @var list<array{int, string, string}> */
    private array $sent = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->sent = [];
        $this->app->instance(OpportunityMessenger::class, new class($this) extends OpportunityMessenger {
            public function __construct(private OpportunityBroadcastTest $t) {}

            public function send(int $chatId, string $text, string $buttonUrl, string $buttonLabel): void
            {
                $this->t->record($chatId, $text, $buttonUrl);
            }
        });
    }

    public function record(int $chatId, string $text, string $url): void
    {
        $this->sent[] = [$chatId, $text, $url];
    }

    /** Возможность, у которой не истёк срок: у остальных рассылка по условию пропускается. */
    private function liveSlug(): string
    {
        foreach (OpportunityFeed::all() as $o) {
            if (empty($o['deadline']) || \Carbon\Carbon::parse($o['deadline'])->endOfDay()->isFuture()) {
                return $o['slug'];
            }
        }

        $this->markTestSkipped('нет возможностей с действующим сроком');
    }

    public function test_first_run_only_marks_existing_opportunities(): void
    {
        BotUser::factory()->create();

        $this->artisan('opportunities:notify')->assertSuccessful();

        $this->assertSame([], $this->sent);
        $this->assertSame(count(OpportunityFeed::all()), DB::table('opportunity_broadcasts')->count());
    }

    public function test_new_opportunity_goes_to_everyone_who_did_not_mute_it_once(): void
    {
        $on = BotUser::factory()->create();
        $banned = BotUser::factory()->banned()->create();
        $muted = BotUser::factory()->create(['notifications_muted' => true]);
        $slug = $this->liveSlug();

        $this->artisan('opportunities:notify')->assertSuccessful();   // первый запуск: пометили всё
        DB::table('opportunity_broadcasts')->where('slug', $slug)->delete();   // «появилась новая»

        $this->artisan('opportunities:notify')->assertSuccessful();

        $ids = array_column($this->sent, 0);
        $this->assertContains((int) $on->telegram_id, $ids);
        $this->assertContains((int) $banned->telegram_id, $ids, 'теневой бан ничего не замечает, рассылка ему приходит как обычно');
        $this->assertNotContains((int) $muted->telegram_id, $ids);
        $this->assertStringContainsString('/app/account/opportunities/'.$slug, $this->sent[0][2]);
        $this->assertStringContainsString('Новая возможность', $this->sent[0][1]);

        // повторный запуск ничего не шлёт
        $before = count($this->sent);
        $this->artisan('opportunities:notify')->assertSuccessful();
        $this->assertCount($before, $this->sent);
    }

    public function test_failed_delivery_is_counted_and_does_not_stop_the_run(): void
    {
        $slug = $this->liveSlug();
        BotUser::factory()->count(2)->create();
        $this->artisan('opportunities:notify')->assertSuccessful();
        DB::table('opportunity_broadcasts')->where('slug', $slug)->delete();

        $this->app->instance(OpportunityMessenger::class, new class extends OpportunityMessenger {
            public function send(int $chatId, string $text, string $buttonUrl, string $buttonLabel): void
            {
                throw new \RuntimeException('Forbidden: bot was blocked by the user');
            }
        });

        $this->artisan('opportunities:notify')->assertSuccessful();

        $row = DB::table('opportunity_broadcasts')->where('slug', $slug)->first();
        $this->assertSame(0, (int) $row->recipients);
        $this->assertSame(2, (int) $row->failed);
    }

    public function test_notification_checkbox_in_the_profile_mutes_and_unmutes(): void
    {
        $u = BotUser::factory()->create();
        $session = ['account_telegram_id' => $u->telegram_id, '_account_expires' => now()->addDay()->timestamp];

        $this->withSession($session)->post(route('account.profile.update'), ['full_name' => 'Test User', 'notifications' => '0'])->assertRedirect();
        $this->assertTrue($u->fresh()->notifications_muted);

        $this->withSession($session)->post(route('account.profile.update'), ['full_name' => 'Test User', 'notifications' => '1'])->assertRedirect();
        $this->assertFalse($u->fresh()->notifications_muted);

        // форма без этого поля настройку не трогает
        $u->update(['notifications_muted' => true]);
        $this->withSession($session)->post(route('account.profile.update'), ['full_name' => 'Test User'])->assertRedirect();
        $this->assertTrue($u->fresh()->notifications_muted);
    }
}
