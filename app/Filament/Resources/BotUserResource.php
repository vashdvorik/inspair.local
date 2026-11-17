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
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables;
use Filament\Tables\Table;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardRemove;
use Illuminate\Support\Facades\Log;
use Nutgram\Laravel\Facades\Telegram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

class BotUserResource extends Resource
{
    protected static ?string $model = BotUser::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Заявки';

    protected static ?string $modelLabel = 'Заявка';

    protected static ?string $pluralModelLabel = 'Заявки';

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
                TextInput::make('contact_instagram')->label('Instagram')->disabled(),
            ])->columns(3),

            Section::make('Анкета')->schema([
                TextInput::make('full_name')
                    ->label('Имя и фамилия'),
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
                        BotUser::STATUS_PENDING  => 'Ожидает',
                        BotUser::STATUS_APPROVED => 'Одобрен',
                        BotUser::STATUS_REJECTED => 'Отклонён',
                    ]),
                DateTimePicker::make('approved_at')
                    ->label('Дата одобрения')
                    ->disabled(),
                Textarea::make('moderation_note')
                    ->label('Сообщение участнику')
                    ->helperText('Показывается в кабинете на страницах «Игра» и «Участники», если доступ закрыт.')
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
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Статус')
                    ->colors([
                        'warning' => BotUser::STATUS_PENDING,
                        'success' => BotUser::STATUS_APPROVED,
                        'danger'  => BotUser::STATUS_REJECTED,
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        BotUser::STATUS_PENDING  => 'Ожидает',
                        BotUser::STATUS_APPROVED => 'Одобрен',
                        BotUser::STATUS_REJECTED => 'Отклонён',
                        default                  => $state,
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Подал заявку')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Статус')
                    ->options([
                        BotUser::STATUS_PENDING  => 'Ожидает',
                        BotUser::STATUS_APPROVED => 'Одобрен',
                        BotUser::STATUS_REJECTED => 'Отклонён',
                    ]),
            ])
            ->actions([
                Action::make('approve')
                    ->label('Одобрить')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (BotUser $record): bool => ! $record->isApproved())
                    ->requiresConfirmation()
                    ->action(function (BotUser $record): void {
                        $record->update([
                            'status'          => BotUser::STATUS_APPROVED,
                            'approved_at'     => now(),
                            'moderation_note' => null,
                        ]);

                        self::sendApprovalMessage($record);

                        Notification::make()
                            ->title('Заявка одобрена')
                            ->success()
                            ->send();
                    }),

                Action::make('reject')
                    ->label('Отклонить')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (BotUser $record): bool => ! $record->isRejected())
                    ->modalHeading('Отказать в доступе к игре')
                    ->modalDescription('Участник по-прежнему сможет войти в кабинет, но игра и список участников останутся закрытыми. Сообщение ниже он увидит в кабинете.')
                    ->form([
                        Textarea::make('note')
                            ->label('Сообщение участнику')
                            ->placeholder('Например: профиль не заполнен, добавьте информацию о себе и напишите нам.')
                            ->rows(4)
                            ->maxLength(1000),
                    ])
                    ->action(function (BotUser $record, array $data): void {
                        $wasApproved = $record->isApproved();

                        $record->update([
                            'status'          => BotUser::STATUS_REJECTED,
                            'moderation_note' => filled($data['note'] ?? null) ? trim((string) $data['note']) : null,
                        ]);
                        $record->refresh();

                        if ($wasApproved) {
                            self::sendAccessRevokedMessage($record);
                        } else {
                            self::sendRejectionMessage($record);
                        }

                        Notification::make()
                            ->title('Заявка отклонена')
                            ->warning()
                            ->send();
                    }),

                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
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

    private static function sendApprovalMessage(BotUser $record): void
    {
        $firstName = explode(' ', (string) $record->full_name)[0];

        $inlineKeyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('С чего начать? →', callback_data: 'start_guide')
            );

        // перечисляем только то, что в кабинете реально есть; игру упоминаем, когда она включена
        $gameLine = app(\App\Services\GameService::class)->isEnabled()
            ? "\n🎮 игра «Угадай, кто это»: отвечай на вопросы, копи очки и знакомься с участниками"
            : '';
        $approvalText = "🎉 {$firstName}, добро пожаловать в Инспайр!\n\nМодератор одобрил твой профиль. Теперь в личном кабинете тебе доступны:\n\n👥 список участников сообщества\n📅 возможности: учебные курсы и мероприятия за рубежом{$gameLine}\n\nКабинет открывается по кнопке «Кабинет» рядом с полем ввода сообщения.";

        try {
            Telegram::sendMessage(
                chat_id: $record->telegram_id,
                text: $approvalText,
                reply_markup: ReplyKeyboardRemove::make(true),
            );

            Telegram::sendMessage(
                chat_id: $record->telegram_id,
                text: "С чего начать?",
                reply_markup: $inlineKeyboard,
            );
        } catch (\Throwable $e) {
            Log::error('Telegram: не удалось отправить сообщение об одобрении', [
                'telegram_id' => $record->telegram_id,
                'error'       => $e->getMessage(),
            ]);
        }
    }

    private static function sendRejectionMessage(BotUser $record): void
    {
        $firstName = explode(' ', (string) $record->full_name)[0];

        try {
            Telegram::sendMessage(
                chat_id: $record->telegram_id,
                text: "😔 {$firstName}, модератор отказал тебе в доступе к игре." . (filled($record->moderation_note) ? "\n\nСообщение модератора:\n{$record->moderation_note}" : '') . "\n\nЛичный кабинет остаётся открытым. Если есть вопросы — напиши напрямую: @lesnichenkoP",
            );
        } catch (\Throwable $e) {
            Log::error('Telegram: не удалось отправить сообщение об отклонении', [
                'telegram_id' => $record->telegram_id,
                'error'       => $e->getMessage(),
            ]);
        }
    }

    private static function sendAccessRevokedMessage(BotUser $record): void
    {
        try {
            Telegram::sendMessage(
                chat_id: $record->telegram_id,
                text: "🔒 Модератор закрыл тебе доступ к игре." . (filled($record->moderation_note) ? "\n\nСообщение модератора:\n{$record->moderation_note}" : '') . "\n\nЛичный кабинет остаётся открытым. Если есть вопросы — напиши напрямую: @lesnichenkoP",
                reply_markup: ReplyKeyboardRemove::make(remove_keyboard: true),
            );
        } catch (\Throwable $e) {
            Log::error('Telegram: не удалось отправить сообщение об отзыве доступа', [
                'telegram_id' => $record->telegram_id,
                'error'       => $e->getMessage(),
            ]);
        }
    }
}