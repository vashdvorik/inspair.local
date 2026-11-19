<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\BotUserResource\Pages\ListBotUsers;
use App\Models\BotUser;
use App\Models\User;
use App\Support\BotUserExport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use OpenSpout\Reader\XLSX\Reader;
use Tests\TestCase;

class AdminExportTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<int, array<int, mixed>> */
    private function readBack(): array
    {
        $path = tempnam(sys_get_temp_dir(), 'x').'.xlsx';
        app(BotUserExport::class)->writeTo($path);

        $reader = new Reader();
        $reader->open($path);
        $rows = [];
        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $rows[] = $row->toArray();
            }
        }
        $reader->close();
        @unlink($path);

        return $rows;
    }

    public function test_export_contains_only_the_registration_fields(): void
    {
        BotUser::factory()->create([
            'full_name' => 'Анна Лунгу', 'organization' => 'УСМ, преподаватель', 'contact_phone' => '+373 600 11111',
            'contact_email' => 'anna@example.com', 'city' => 'Кишинёв', 'description' => 'секрет о себе', 'expectation' => 'секрет ожиданий',
        ]);

        $rows = $this->readBack();

        $this->assertSame(['Имя и фамилия', 'Университет/организация и должность', 'Телефон', 'Email', 'Город'], $rows[0]);
        $this->assertSame(['Анна Лунгу', 'УСМ, преподаватель', '+373 600 11111', 'anna@example.com', 'Кишинёв'], $rows[1]);
        $this->assertCount(2, $rows);
        $this->assertStringNotContainsString('секрет', json_encode($rows, JSON_UNESCAPED_UNICODE));
    }

    public function test_formula_like_text_stays_plain_text(): void
    {
        BotUser::factory()->create(['full_name' => '=HYPERLINK("x")']);

        $this->assertSame('=HYPERLINK("x")', $this->readBack()[1][0]);
    }

    public function test_column_widths_fit_the_longest_value_within_limits(): void
    {
        $export = app(BotUserExport::class);
        $rows = [['Анна', str_repeat('я', 160), '+373 600 11111', 'a@e.com', 'Кишинёв']];

        $w = $export->widths($rows);

        $this->assertSame(60.0, $w[1], 'длинная организация не шире предела, остальное переносится');
        $this->assertGreaterThanOrEqual(14.0, $w[0]);
        $this->assertGreaterThanOrEqual(strlen('+373 600 11111'), $w[2]);
    }

    public function test_admin_action_downloads_an_xlsx_file(): void
    {
        $this->actingAs(User::factory()->create());
        BotUser::factory()->create();

        ob_start();
        Livewire::test(ListBotUsers::class)->callAction('export')->assertHasNoActionErrors();
        ob_end_clean();
    }
}
