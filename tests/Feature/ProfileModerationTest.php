<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BotUser;
use App\Models\ModerationFlag;
use App\Models\ProfileFact;
use App\Services\Ai\OpenRouter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Tests\TestCase;

/** Нейросеть проверяет тексты участников и предупреждает администратора; решения принимает только он. */
class ProfileModerationTest extends TestCase
{
    use RefreshDatabase;

    /** @var list<string> тексты, которые ушли на проверку */
    private array $asked = [];

    private function ai(array $flags): MockInterface
    {
        return $this->mock(OpenRouter::class, function (MockInterface $m) use ($flags): void {
            $m->shouldReceive('json')->andReturnUsing(function (string $task, array $messages) use ($flags) {
                $this->asked[] = $messages[1]['content'];

                return ['flags' => $flags];
            });
        });
    }

    public function test_suspicious_text_becomes_a_warning_and_nothing_is_blocked(): void
    {
        $u = BotUser::factory()->create(['full_name' => 'Иван Петров', 'description' => 'Куплю дёшево, пишите в личку!!!']);
        $this->ai([['id' => 'about', 'category' => 'scam', 'severity' => 'high', 'reason' => 'Реклама в описании']]);

        $this->artisan('moderation:scan')->assertSuccessful();

        $flag = ModerationFlag::firstOrFail();
        $this->assertSame($u->id, $flag->bot_user_id);
        $this->assertSame('about', $flag->field);
        $this->assertSame('scam', $flag->category);
        $this->assertSame('high', $flag->severity);
        $this->assertSame('Куплю дёшево, пишите в личку!!!', $flag->excerpt);
        $this->assertSame(ModerationFlag::OPEN, $flag->status);
        $this->assertTrue($u->fresh()->isApproved(), 'нейросеть только предупреждает, участника не блокирует');
    }

    public function test_unchanged_texts_are_not_checked_again_and_changed_ones_are(): void
    {
        $u = BotUser::factory()->create(['full_name' => 'Анна Кох', 'description' => 'Студентка, люблю науку']);
        $this->ai([]);

        $this->artisan('moderation:scan')->assertSuccessful();
        $this->artisan('moderation:scan')->assertSuccessful();
        $this->assertCount(1, $this->asked, 'второй запуск ничего не меняет и нейросеть не трогает');

        $u->update(['description' => 'Студентка, люблю науку и музыку']);
        $this->artisan('moderation:scan')->assertSuccessful();
        $this->assertCount(2, $this->asked);
    }

    public function test_facts_and_names_are_checked_and_the_same_text_is_not_flagged_twice(): void
    {
        $u = BotUser::factory()->create(['full_name' => 'Козёл Иванов']);
        $fact = ProfileFact::create(['bot_user_id' => $u->id, 'body' => 'Ненавижу всех']);
        $this->ai([
            ['id' => 'name', 'category' => 'abuse', 'severity' => 'medium', 'reason' => 'Оскорбительное имя'],
            ['id' => 'fact_'.$fact->id, 'category' => 'hate', 'severity' => 'low', 'reason' => ''],
            ['id' => 'unknown_id', 'category' => 'abuse'],   // выдуманный нейросетью id пропускается
        ]);

        $this->artisan('moderation:scan')->assertSuccessful();
        $this->assertSame(2, ModerationFlag::count());
        $this->assertStringContainsString('Ненавижу всех', $this->asked[0]);

        // администратор признал имя нормальным; после правки другого поля то же имя не поднимается снова
        ModerationFlag::where('field', 'name')->update(['status' => ModerationFlag::DISMISSED]);
        $u->update(['description' => 'Новый текст о себе']);
        $this->artisan('moderation:scan')->assertSuccessful();

        $this->assertSame(2, ModerationFlag::count());
    }

    public function test_without_ai_the_scan_stops_quietly_and_retries_later(): void
    {
        $u = BotUser::factory()->create(['description' => 'Привет']);
        $this->mock(OpenRouter::class, fn (MockInterface $m) => $m->shouldReceive('json')->andThrow(new \RuntimeException('Не задан OPENROUTER_API_KEY.')));

        $this->artisan('moderation:scan')->assertSuccessful();

        $this->assertSame(0, ModerationFlag::count());
        $this->assertNull($u->fresh()->moderated_hash, 'при сбое тексты не считаются проверенными');
    }

    public function test_banned_participants_are_skipped(): void
    {
        BotUser::factory()->banned()->create(['description' => 'что-то']);
        $this->ai([]);

        $this->artisan('moderation:scan')->assertSuccessful();

        $this->assertSame([], $this->asked);
    }

    public function test_admin_sees_warnings_and_can_hide_the_participant_or_dismiss(): void
    {
        $this->actingAs(\App\Models\User::factory()->create());
        $u = BotUser::factory()->create(['full_name' => 'Нарушитель']);
        $other = BotUser::factory()->create();
        $hide = ModerationFlag::create(['bot_user_id' => $u->id, 'field' => 'about', 'excerpt' => 'плохой текст', 'excerpt_hash' => sha1('плохой текст'), 'category' => 'abuse', 'severity' => 'high', 'reason' => 'Оскорбления']);
        $ok = ModerationFlag::create(['bot_user_id' => $other->id, 'field' => 'name', 'excerpt' => 'Вася', 'excerpt_hash' => sha1('Вася'), 'category' => 'fake', 'severity' => 'low']);

        $this->get('/admin/moderation-flags')->assertOk()->assertSee('Нарушитель')->assertSee('плохой текст');

        \Livewire\Livewire::test(\App\Filament\Resources\ModerationFlagResource\Pages\ListModerationFlags::class)
            ->callAction(\Filament\Actions\Testing\TestAction::make('ban')->table($hide))
            ->callAction(\Filament\Actions\Testing\TestAction::make('dismiss')->table($ok));

        $this->assertTrue($u->fresh()->isBanned());
        $this->assertSame(ModerationFlag::BANNED, $hide->fresh()->status);
        $this->assertSame(ModerationFlag::DISMISSED, $ok->fresh()->status);
        $this->assertTrue($other->fresh()->isApproved());
    }

    /** @return list<array{int, string, string}> что ушло администраторам: [chat_id, текст, данные кнопок] */
    private function captureAdminMessages(): object
    {
        $log = new class {
            public array $sent = [];
        };

        $this->app->instance(\App\Services\ModerationNotifier::class, new class($log) extends \App\Services\ModerationNotifier {
            public function __construct(private object $log) {}

            protected function deliver(int $chatId, string $text, \SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup $markup): void
            {
                $this->log->sent[] = [$chatId, $text, json_encode($markup->inline_keyboard)];
            }
        });

        return $log;
    }

    public function test_admins_get_a_telegram_message_with_decision_buttons(): void
    {
        config(['moderation.admin_telegram_ids' => [555001]]);
        $author = BotUser::factory()->create(['full_name' => 'Нарушитель Иван', 'telegram_username' => 'bad_ivan', 'description' => 'Куплю дёшево']);
        $moderator = BotUser::factory()->moderator()->banned()->create();   // забаненных не проверяют, но предупреждения они получают
        $this->ai([['id' => 'about', 'category' => 'scam', 'severity' => 'high', 'reason' => 'Реклама']]);
        $log = $this->captureAdminMessages();

        $this->artisan('moderation:scan')->assertSuccessful();

        $chats = array_column($log->sent, 0);
        $this->assertEqualsCanonicalizing([555001, (int) $moderator->telegram_id], $chats);
        [, $text, $buttons] = $log->sent[0];
        $this->assertStringContainsString('Нарушитель Иван (@bad_ivan)', $text);
        $this->assertStringContainsString('Реклама', $text);
        $this->assertStringContainsString('«Куплю дёшево»', $text);
        $this->assertStringContainsString("mod:ban:{$author->id}", $buttons);
        $this->assertStringContainsString("mod:ok:{$author->id}", $buttons);
    }

    public function test_no_message_when_nothing_suspicious_and_only_admins_may_press_the_buttons(): void
    {
        config(['moderation.admin_telegram_ids' => [555001]]);
        $ordinary = BotUser::factory()->create(['description' => 'Привет']);
        $this->ai([]);
        $log = $this->captureAdminMessages();

        $this->artisan('moderation:scan')->assertSuccessful();

        $this->assertSame([], $log->sent);
        $this->assertTrue(\App\Services\ModerationNotifier::isAdmin(555001));
        $this->assertFalse(\App\Services\ModerationNotifier::isAdmin((int) $ordinary->telegram_id));
    }

    public function test_participants_list_in_the_panel_opens_and_marks_people_with_open_warnings(): void
    {
        $this->actingAs(\App\Models\User::factory()->create());
        $u = BotUser::factory()->create(['full_name' => 'Человек с флагом']);
        ModerationFlag::create(['bot_user_id' => $u->id, 'field' => 'about', 'excerpt' => 'x', 'excerpt_hash' => sha1('x'), 'category' => 'abuse']);

        $this->get('/admin/bot-users')->assertOk()->assertSee('Человек с флагом')->assertSee('⚠ 1');
    }

    public function test_telegram_buttons_use_the_same_actions_as_the_panel(): void
    {
        $u = BotUser::factory()->create();
        $a = ModerationFlag::create(['bot_user_id' => $u->id, 'field' => 'about', 'excerpt' => 'x', 'excerpt_hash' => sha1('x'), 'category' => 'abuse']);
        $b = ModerationFlag::create(['bot_user_id' => $u->id, 'field' => 'name', 'excerpt' => 'y', 'excerpt_hash' => sha1('y'), 'category' => 'fake']);

        $this->assertSame(2, \App\Services\ModerationActions::dismiss($u));
        $this->assertSame(ModerationFlag::DISMISSED, $a->fresh()->status);
        $this->assertTrue($u->fresh()->isApproved());

        $c = ModerationFlag::create(['bot_user_id' => $u->id, 'field' => 'fact', 'excerpt' => 'z', 'excerpt_hash' => sha1('z'), 'category' => 'hate']);
        \App\Services\ModerationActions::ban($u, 'Модерация: тест');
        $this->assertTrue($u->fresh()->isBanned());
        $this->assertSame('Модерация: тест', $u->fresh()->moderation_note);
        $this->assertSame(ModerationFlag::BANNED, $c->fresh()->status);
        $this->assertSame(ModerationFlag::DISMISSED, $b->fresh()->status, 'уже закрытые предупреждения не меняются');
    }
}
