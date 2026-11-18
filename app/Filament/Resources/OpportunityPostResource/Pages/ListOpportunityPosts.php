<?php

declare(strict_types=1);

namespace App\Filament\Resources\OpportunityPostResource\Pages;

use App\Filament\Resources\OpportunityPostResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOpportunityPosts extends ListRecords
{
    protected static string $resource = OpportunityPostResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Добавить возможность')];
    }
}
