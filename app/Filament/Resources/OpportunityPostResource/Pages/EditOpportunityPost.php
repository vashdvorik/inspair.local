<?php

declare(strict_types=1);

namespace App\Filament\Resources\OpportunityPostResource\Pages;

use App\Filament\Resources\OpportunityPostResource;
use App\Models\OpportunityPost;
use Filament\Resources\Pages\EditRecord;

class EditOpportunityPost extends EditRecord
{
    protected static string $resource = OpportunityPostResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return OpportunityPost::fillMissingLanguages($data);
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
