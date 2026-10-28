<?php

declare(strict_types=1);

namespace Tests\Feature\Game;

use App\Models\BotUser;
use App\Models\Connection;
use App\Models\ProfileFact;
use App\Models\Quest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GameMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_game_migration_can_be_run_again_after_a_failed_run(): void
    {
        $migration = require database_path('migrations/2026_10_18_110000_create_game_tables.php');

        $migration->up(); // tables and columns already exist: must not fail
        $migration->up();

        $this->assertTrue(\Illuminate\Support\Facades\Schema::hasTable('profile_facts'));
        $this->assertTrue(\Illuminate\Support\Facades\Schema::hasColumn('bot_users', 'xp'));
    }

    public function test_deleting_a_user_removes_game_data_even_without_foreign_keys(): void
    {
        $a = BotUser::factory()->approved()->create();
        $b = BotUser::factory()->approved()->create();
        ProfileFact::create(['bot_user_id' => $a->id, 'body' => 'факт']);
        Connection::create(['user_id' => $a->id, 'target_id' => $b->id, 'source' => 'quest']);
        Connection::create(['user_id' => $b->id, 'target_id' => $a->id, 'source' => 'quest']);
        $quest = Quest::create(['subject_user_id' => $a->id, 'option_ids' => [$a->id, $b->id], 'question' => ['ru' => 'q'], 'explanation' => ['ru' => 'e'], 'status' => 'active']);
        DB::table('quest_attempts')->insert(['bot_user_id' => $b->id, 'quest_id' => $quest->id, 'chosen_user_id' => $a->id, 'correct' => true]);

        $a->delete();

        $this->assertSame(0, ProfileFact::count());
        $this->assertSame(0, Connection::count());
        $this->assertSame(0, Quest::count());
        $this->assertSame(0, DB::table('quest_attempts')->count());
        $this->assertNotNull(BotUser::find($b->id));
    }
}
