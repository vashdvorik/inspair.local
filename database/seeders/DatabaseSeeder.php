<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Demo data must never reach a live site (set ALLOW_DEMO_SEED=true only for a deliberate test)
        if (app()->isProduction() && ! filter_var(env('ALLOW_DEMO_SEED', false), FILTER_VALIDATE_BOOLEAN)) {
            $this->command?->warn('DatabaseSeeder: skipped in production.');

            return;
        }

        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->call([
            BotUserSeeder::class,
            CommunitySeeder::class,
        ]);
    }
}
