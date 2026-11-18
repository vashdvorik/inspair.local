<?php

declare(strict_types=1);

namespace App\Filament\Resources\BroadcastResource\Pages;

use App\Filament\Resources\BroadcastResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBroadcast extends CreateRecord
{
    protected static string $resource = BroadcastResource::class;

    protected static bool $canCreateAnother = false;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
