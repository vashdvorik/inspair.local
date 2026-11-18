<?php

declare(strict_types=1);

namespace App\Filament\Resources\BroadcastResource\Pages;

use App\Filament\Resources\BroadcastResource;
use Filament\Resources\Pages\EditRecord;

class EditBroadcast extends EditRecord
{
    protected static string $resource = BroadcastResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
