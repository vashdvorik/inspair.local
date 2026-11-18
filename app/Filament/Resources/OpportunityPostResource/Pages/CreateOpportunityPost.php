<?php

declare(strict_types=1);

namespace App\Filament\Resources\OpportunityPostResource\Pages;

use App\Filament\Resources\OpportunityPostResource;
use App\Models\OpportunityPost;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;

class CreateOpportunityPost extends CreateRecord
{
    protected static string $resource = OpportunityPostResource::class;

    protected static bool $canCreateAnother = false;

    private bool $notifyBot = true;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->notifyBot = (bool) ($data['notify_bot'] ?? true);
        unset($data['notify_bot']);

        return OpportunityPost::fillMissingLanguages($data);
    }

    protected function afterCreate(): void
    {
        // новая возможность уйдёт в бот сама (команда opportunities:notify); если рассылка не нужна, помечаем её «уже была»
        if (! $this->notifyBot) {
            DB::table('opportunity_broadcasts')->insertOrIgnore(['slug' => $this->record->slug, 'baseline' => true, 'created_at' => now(), 'updated_at' => now()]);
        }
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
