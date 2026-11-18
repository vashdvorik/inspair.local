<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ModerationFlagResource\Pages;
use App\Models\BotUser;
use App\Models\ModerationFlag;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/** Предупреждения нейросети о подозрительных текстах участников: администратор решает, что с ними делать. */
class ModerationFlagResource extends Resource
{
    protected static ?string $model = ModerationFlag::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shield-exclamation';

    protected static ?string $navigationLabel = 'Модерация';

    protected static ?string $modelLabel = 'Предупреждение';

    protected static ?string $pluralModelLabel = 'Модерация';

    protected static ?int $navigationSort = 3;

    public static function getNavigationBadge(): ?string
    {
        $open = ModerationFlag::where('status', ModerationFlag::OPEN)->count();

        return $open > 0 ? (string) $open : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Нейросеть нашла подозрительные тексты участников';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('user.full_name')->label('Участник')->description(fn (ModerationFlag $r): ?string => $r->user?->telegram_username ? '@'.$r->user->telegram_username : null)->searchable(),
                Tables\Columns\TextColumn::make('field')->label('Где')->formatStateUsing(fn (string $state): string => ModerationFlag::FIELDS[$state] ?? $state),
                Tables\Columns\TextColumn::make('excerpt')->label('Текст')->limit(160)->wrap()->tooltip(fn (ModerationFlag $r): string => $r->excerpt),
                Tables\Columns\TextColumn::make('category')
                    ->label('Что не так')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'sexual', 'hate' => 'danger',
                        'abuse', 'scam' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ModerationFlag::CATEGORIES[$state] ?? $state)
                    ->description(fn (ModerationFlag $r): ?string => $r->reason),
                Tables\Columns\TextColumn::make('severity')
                    ->label('Насколько')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) { 'high' => 'danger', 'medium' => 'warning', default => 'gray' })
                    ->formatStateUsing(fn (string $state): string => match ($state) { 'high' => 'серьёзно', 'medium' => 'похоже', default => 'возможно' }),
                Tables\Columns\TextColumn::make('status')
                    ->label('Решение')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) { ModerationFlag::OPEN => 'danger', ModerationFlag::BANNED => 'warning', default => 'success' })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        ModerationFlag::OPEN => 'Ждёт решения',
                        ModerationFlag::BANNED => 'Участник скрыт',
                        default => 'Нарушения нет',
                    }),
                Tables\Columns\TextColumn::make('created_at')->label('Найдено')->dateTime('d.m.Y H:i'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Решение')
                    ->options([ModerationFlag::OPEN => 'Ждёт решения', ModerationFlag::BANNED => 'Участник скрыт', ModerationFlag::DISMISSED => 'Нарушения нет'])
                    ->default(ModerationFlag::OPEN),
            ])
            ->actions([
                ActionGroup::make([
                    Action::make('ban')
                        ->label('Скрыть участника')
                        ->icon('heroicon-o-eye-slash')
                        ->color('danger')
                        ->visible(fn (ModerationFlag $r): bool => $r->status === ModerationFlag::OPEN && $r->user && ! $r->user->isBanned())
                        ->requiresConfirmation()
                        ->modalHeading('Теневой бан')
                        ->modalDescription('Участник продолжит пользоваться всем как обычно и ничего не заметит, но другие перестанут его видеть. Бан можно снять в разделе «Участники».')
                        ->action(function (ModerationFlag $record): void {
                            \App\Services\ModerationActions::ban($record->user, 'Модерация: '.($record->reason ?: (ModerationFlag::CATEGORIES[$record->category] ?? $record->category)));
                            Notification::make()->title('Участник скрыт от других')->warning()->send();
                        }),

                    Action::make('dismiss')
                        ->label('Нарушения нет')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->visible(fn (ModerationFlag $r): bool => $r->status === ModerationFlag::OPEN)
                        ->action(function (ModerationFlag $record): void {
                            $record->update(['status' => ModerationFlag::DISMISSED, 'reviewed_at' => now()]);
                            Notification::make()->title('Отмечено: нарушения нет')->success()->send();
                        }),

                    Action::make('user')
                        ->label('Участник')
                        ->icon('heroicon-o-user')
                        ->color('gray')
                        ->url(fn (ModerationFlag $r): string => BotUserResource::getUrl('edit', ['record' => $r->bot_user_id])),
                ])
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->tooltip('Действия'),
            ])
            ->emptyStateHeading('Подозрительного не найдено')
            ->emptyStateDescription('Нейросеть проверяет тексты участников каждые 5 минут и сообщит здесь, если найдёт что-то нарушающее правила.');
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListModerationFlags::route('/')];
    }
}
