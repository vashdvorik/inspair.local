<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\BotUserResource\Pages;
use App\Models\BotUser;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables;
use Filament\Tables\Table;
use Nutgram\Laravel\Facades\Telegram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

class BotUserResource extends Resource
{
    protected static ?string $model = BotUser::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Участники';

    protected static ?string $modelLabel = 'Участник';

    protected static ?string $pluralModelLabel = 'Участники';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Telegram')->schema([
                TextInput::make('telegram_id')
                    ->label('Telegram ID')
                    ->disabled(),
                TextInput::make('telegram_username')
                    ->label('Username')
                    ->disabled(),
            ])->columns(2),

            Section::make('Контакты (видят только администраторы)')->schema([
                TextInput::make('contact_phone')->label('Телефон')->disabled(),
                TextInput::make('contact_email')->label('Почта')->disabled(),
            ])->columns(2),

            Section::make('Анкета')->schema([
                TextInput::make('full_name')
                    ->label('Имя и фамилия'),
                TextInput::make('organization')
                    ->label('Университет/организация и должность'),
                TextInput::make('city')
                    ->label('Город'),
                Textarea::make('description')
                    ->label('О себе')
                    ->rows(3),
                Textarea::make('expectation')
                    ->label('Ожидания и польза')
                    ->rows(3),
            ]),

            Section::make('Статус')->schema([
                Select::make('status')
                    ->label('Статус')
                    ->options([
                        BotUser::STATUS_APPROVED => 'Активен',
                        BotUser::STATUS_BANNED   => 'Теневой бан',
                    ])
                    ->helperText('Теневой бан: участник пользуется всем как обычно и ничего не замечает, но другие его не видят (списки, поиск, совпадения, игра).'),
                DateTimePicker::make('approved_at')
                    ->label('Дата регистрации')
                    ->disabled(),
                Textarea::make('moderation_note')
                    ->label('Заметка администратора')
                    ->helperText('Видна только администраторам: например, за что участник заблокирован.')
                    ->rows(3)
                    ->maxLength(1000)
                    ->columnSpanFull(),
            ])->columns(2),

            Section::make('Игра')->schema([
                \Filament\Forms\Components\Toggle::make('is_moderator')
                    ->label('Модератор / организатор')
                    ->helperText('Видит все разделы кабинета без уровней.'),
                TextInput::make('xp')
                    ->label('Очки')
                    ->numeric()
                    ->disabled(),
                TextInput::make('level')
                    ->label('Уровень')
                    ->disabled(),
            ])->columns(3),
        ]);
    }

    /** Число открытых предупреждений нейросети для значка ⚠ в списке. */
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->withCount(['moderationFlags as open_flags_count' => fn ($q) => $q->where('status', 'open')]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Имя')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('telegram_username')
                    ->label('Username')
                    ->formatStateUsing(fn (?string $state): string => $state ? "@{$state}" : '—')
                    ->visibleFrom('md')
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Статус')
                    ->colors([
                        'success' => BotUser::STATUS_APPROVED,
                        'danger'  => BotUser::STATUS_BANNED,
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        BotUser::STATUS_APPROVED => 'Активен',
                        BotUser::STATUS_BANNED   => 'Теневой бан',
                        default                  => $state,
                    }),
                Tables\Columns\TextColumn::make('open_flags_count')
                    ->label('⚠')
                    ->badge()
                    ->color('danger')
                    ->formatStateUsing(fn ($state): string => $state ? '⚠ '.$state : '')
                    ->tooltip('Нейросеть нашла подозрительное в текстах: раздел «Модерация»'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Зарегистрирован')
                    ->dateTime('d.m.Y H:i')
                    ->visibleFrom('lg')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Статус')
                    ->options([
                        BotUser::STATUS_APPROVED => 'Активен',
                        BotUser::STATUS_BANNED   => 'Теневой бан',
                    ]),
            ])
            ->actions([
                ActionGroup::make([
                    Action::make('ban')
                        ->label('Теневой бан')
                        ->icon('heroicon-o-eye-slash')
                        ->color('danger')
                        ->visible(fn (BotUser $record): bool => ! $record->isBanned())
                        ->modalHeading('Теневой бан')
                        ->modalDescription('Участник продолжит пользоваться кабинетом и игрой как обычно и не получит никаких сообщений, но другие участники перестанут его видеть: в списках, поиске, совпадениях и игре. Это можно отменить в любой момент.')
                        ->form([
                            Textarea::make('note')
                                ->label('Заметка для администраторов (необязательно)')
                                ->placeholder('Например: спам в профиле.')
                                ->rows(3)
                                ->maxLength(1000),
                        ])
                        ->action(function (BotUser $record, array $data): void {
                            $record->update([
                                'status'          => BotUser::STATUS_BANNED,
                                'moderation_note' => filled($data['note'] ?? null) ? trim((string) $data['note']) : $record->moderation_note,
                            ]);

                            Notification::make()->title('Участник скрыт от других')->warning()->send();
                        }),

                    Action::make('unban')
                        ->label('Снять бан')
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->visible(fn (BotUser $record): bool => $record->isBanned())
                        ->requiresConfirmation()
                        ->action(function (BotUser $record): void {
                            $record->update(['status' => BotUser::STATUS_APPROVED]);

                            Notification::make()->title('Участник снова виден всем')->success()->send();
                        }),

                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ])
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->tooltip('Действия'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBotUsers::route('/'),
            'view'  => Pages\ViewBotUser::route('/{record}'),
            'edit'  => Pages\EditBotUser::route('/{record}/edit'),
        ];
    }
}
