<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BotUser;
use App\Models\OpportunityPost;
use App\Models\User;
use App\Support\OpportunityFeed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/** «Возможности» добавляются в админке и сразу появляются на сайте и в кабинете. */
class AdminOpportunityTest extends TestCase
{
    use RefreshDatabase;

    private function makePost(array $attrs = []): OpportunityPost
    {
        return OpportunityPost::create($attrs + [
            'slug' => 'test-trip', 'name' => 'Test Trip', 'flag' => '🇩🇪',
            'country' => ['ru' => 'Германия', 'en' => 'Germany', 'ro' => 'Germania'],
            'text' => ['ru' => 'Курс в Берлине', 'en' => 'A course in Berlin', 'ro' => 'Curs la Berlin'],
            'detail' => ['en' => ['lead' => 'Lead', 'explore' => ['one'], 'format' => 'workshops', 'benefits' => ['two'], 'audience' => 'students']],
            'image' => 'test.webp', 'posted_on' => now()->toDateString(), 'published' => true,
        ]);
    }

    public function test_existing_opportunities_were_imported_from_the_json_file(): void
    {
        $this->assertGreaterThanOrEqual(14, OpportunityPost::count());
        $this->assertNotNull(OpportunityFeed::find('emotion-armenia'));
    }

    public function test_old_cached_feed_without_language_titles_does_not_break_pages(): void
    {
        // после выкладки на сервере мог остаться кэш старой формы (без поля title): страницы не должны падать
        \Illuminate\Support\Facades\Cache::forever('opportunity_feed:v2', [[
            'slug' => 'old-one', 'name' => 'Old Cached Trip', 'flag' => null, 'country' => ['ru' => 'X', 'en' => 'X', 'ro' => 'X'],
            'start' => null, 'end' => null, 'deadline' => null, 'date' => '2026-01-01',
            'text' => ['ru' => 't', 'en' => 't', 'ro' => 't'], 'detail' => ['ru' => [], 'en' => [], 'ro' => []], 'image' => 'a.webp', 'url' => null,
        ]]);

        $this->get('/opportunities')->assertOk()->assertSee('Old Cached Trip');
        $this->get('/opportunities/old-one')->assertOk();
    }

    public function test_new_opportunity_appears_on_site_and_in_cabinet_and_unpublished_one_does_not(): void
    {
        $this->get('/opportunities')->assertOk()->assertDontSee('Test Trip');
        $p = $this->makePost();

        $this->get('/opportunities')->assertOk()->assertSee('Test Trip');
        $this->get('/opportunities/test-trip')->assertOk();

        $u = BotUser::factory()->create();
        $session = ['account_telegram_id' => $u->telegram_id, '_account_expires' => now()->addDay()->timestamp];
        $this->withSession($session)->get(route('account.opportunities.index'))->assertOk()->assertSee('Test Trip');
        $this->withSession($session)->get(route('account.opportunities.show', 'test-trip'))->assertOk();

        $p->update(['published' => false]);   // кэш ленты сбрасывается сам
        $this->get('/opportunities/test-trip')->assertNotFound();
        $this->get('/opportunities')->assertDontSee('Test Trip');
    }

    public function test_admin_adds_an_opportunity_in_the_panel_and_missing_languages_fall_back_to_english(): void
    {
        $this->actingAs(User::factory()->create());
        Storage::fake('opportunity_images');

        $this->get('/admin/opportunity-posts')->assertOk()->assertSee('Возможности');
        $this->get('/admin/opportunity-posts/create')->assertOk()->assertSee('Разослать участникам');

        Livewire::test(\App\Filament\Resources\OpportunityPostResource\Pages\CreateOpportunityPost::class)
            ->fillForm([
                'name' => 'Green Youth Camp',
                'slug' => 'green-youth-camp',
                'flag' => '🇵🇱',
                'posted_on' => now()->toDateString(),
                'published' => true,
                'notify_bot' => false,
                'image' => UploadedFile::fake()->image('city.jpg', 1200, 800),
                'country.en' => 'Poland',
                'text.en' => 'A camp about ecology.',
                'detail.en.lead' => 'Lead text',
                'detail.en.explore' => "recycling\nclimate action",
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $p = OpportunityPost::where('slug', 'green-youth-camp')->firstOrFail();
        $this->assertSame('Poland', $p->country['en']);
        $this->assertSame('Poland', $p->country['ru'], 'пустой русский заполняется английским');
        $this->assertSame('A camp about ecology.', $p->text['ro']);
        $this->assertSame(['recycling', 'climate action'], $p->detail['en']['explore']);
        $this->assertSame(['recycling', 'climate action'], $p->detail['ro']['explore']);

        // рассылку выключили: бот про неё писать не будет
        $this->assertTrue(DB::table('opportunity_broadcasts')->where('slug', 'green-youth-camp')->where('baseline', true)->exists());
        $this->get('/opportunities/green-youth-camp')->assertOk()->assertSee('Green Youth Camp');
    }
}
