<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\BotUserResource\Pages\ListBotUsers;
use App\Models\BotUser;
use App\Models\ProfileFact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_download_all_participants_as_csv(): void
    {
        $this->actingAs(User::factory()->create());
        $a = BotUser::factory()->create(['full_name' => 'Анна Лунгу', 'organization' => 'УСМ', 'contact_phone' => '+373 600 11111', 'contact_email' => 'anna@example.com', 'consent_at' => now()]);
        BotUser::factory()->banned()->create(['full_name' => '=HYPERLINK("x")']);
        ProfileFact::create(['bot_user_id' => $a->id, 'body' => 'Люблю горы']);

        ob_start();
        Livewire::test(ListBotUsers::class)->callAction('export')->assertHasNoActionErrors();
        ob_end_clean();
    }

    public function test_rows_contain_all_data_and_are_safe_for_excel(): void
    {
        $a = BotUser::factory()->create(['full_name' => 'Анна', 'organization' => 'УСМ', 'contact_phone' => '+373 600 11111', 'contact_email' => 'a@e.com']);
        ProfileFact::create(['bot_user_id' => $a->id, 'body' => 'Люблю горы']);
        $evil = BotUser::factory()->banned()->create(['full_name' => '=HYPERLINK("x")']);

        $export = app(\App\Support\BotUserExport::class);
        $row = $export->row($a->load('facts'));

        $this->assertContains('УСМ', $row);
        $this->assertContains('+373 600 11111', $row, 'телефон остаётся без апострофа');
        $this->assertContains('a@e.com', $row);
        $this->assertContains('@'.$a->telegram_username, $row, 'username без апострофа');
        $this->assertContains('Люблю горы', $row);
        $this->assertContains("'=HYPERLINK(\"x\")", $export->row($evil->load('facts')));
        $this->assertContains('теневой бан', $export->row($evil));
    }

    public function test_download_response_is_utf8_csv_with_bom(): void
    {
        BotUser::factory()->create(['full_name' => 'Анна Лунгу']);

        ob_start();
        $response = app(\App\Support\BotUserExport::class)->download();
        $response->sendContent();
        $body = ob_get_clean();

        $this->assertStringStartsWith("\xEF\xBB\xBF", $body);
        $this->assertStringContainsString('Имя и фамилия', $body);
        $this->assertStringContainsString('Анна Лунгу', $body);
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
    }
}
