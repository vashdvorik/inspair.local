<?php

declare(strict_types=1);

namespace App\Filament\Resources\BotUserResource\Pages;

use App\Filament\Resources\BotUserResource;
use App\Support\BotUserExport;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListBotUsers extends ListRecords
{
    protected static string $resource = BotUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label('Выгрузить всех (Excel)')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(fn () => app(BotUserExport::class)->download()),
        ];
    }
}
