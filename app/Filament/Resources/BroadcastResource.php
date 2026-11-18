<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\BroadcastResource\Pages;
use App\Models\Broadcast;
use App\Services\BroadcastMessenger;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/** Сообщения администратора участникам в бот: текст, картинка, кнопки, расписание. */
class BroadcastResource extends Resource
{
    protected static ?string $model = Broadcast::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $navigationLabel = 'Рассылки';

    protected static ?string $modelLabel = 'Рассылка';

    protected static ?string $pluralModelLabel = 'Рассылки';

    protected static ?int $navigationSort = 2;

    public static function canEdit(Model $record): bool
    {
        return $record->isEditable();
    }

    public static function canDelete(Model $record): bool
    {
        return $record->status !== Broadcast::SENDING;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Сообщение')->schema([
                TextInput::make('title')
                    ->label('Название')
                    ->helperText('Видно только в админке, участники его не получают.')
                    ->required()
                    ->maxLength(150),
                Textarea::make('body')
                    ->label('Текст сообщения')
                    ->helperText('Форматирование как в Telegram HTML: <b>жирный</b>, <i>курсив</i>, <a href="https://…">ссылка</a>. Знаки < > & в обычном тексте пишите как &lt; &gt; &amp;. Перед отправкой пришлите тест себе.')
                    ->required()
                    ->rows(9)
                    ->maxLength(4000)
                    ->columnSpanFull(),
                FileUpload::make('image_path')
                    ->label('Картинка (необязательно)')
                    ->image()
                    ->disk('public')
                    ->directory('broadcasts')
                    ->maxSize(5120)
                    ->helperText('Лучше горизонтальная или квадратная, до 5 МБ. Если текст длиннее 1024 знаков, он придёт отдельным сообщением под картинкой.')
                    ->columnSpanFull(),
            ]),

            Section::make('Кнопки под сообщением')->schema([
                Repeater::make('buttons')
                    ->label('')
                    ->addActionLabel('Добавить кнопку')
                    ->maxItems(5)
                    ->defaultItems(0)
                    ->itemLabel(fn (array $state): ?string => $state['label'] ?? null)
                    ->collapsible()
                    ->schema([
                        TextInput::make('label')->label('Текст на кнопке')->required()->maxLength(64),
                        Select::make('kind')
                            ->label('Что делает кнопка')
                            ->options(['url' => 'Открывает ссылку', 'cabinet' => 'Открывает страницу кабинета в Telegram'])
                            ->default('url')
                            ->live()
                            ->required(),
                        TextInput::make('url')
                            ->label('Ссылка')
                            ->url()
                            ->placeholder('https://')
                            ->visible(fn (Get $get): bool => ($get('kind') ?? 'url') === 'url')
                            ->required(fn (Get $get): bool => ($get('kind') ?? 'url') === 'url'),
                        Select::make('page')
                            ->label('Страница кабинета')
                            ->options(Broadcast::CABINET_PAGES)
                            ->default('')
                            ->visible(fn (Get $get): bool => $get('kind') === 'cabinet'),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]),

            Section::make('Кому и когда')->schema([
                Select::make('audience')
                    ->label('Кому')
                    ->options([
                        Broadcast::AUDIENCE_NOTIFY => 'Тем, кто не отключил уведомления',
                        Broadcast::AUDIENCE_ALL    => 'Всем участникам (важное объявление)',
                    ])
                    ->default(Broadcast::AUDIENCE_NOTIFY)
                    ->required(),
                DateTimePicker::make('scheduled_at')
                    ->label('Отправить не раньше (необязательно)')
                    ->helperText('Пусто: уйдёт сразу после нажатия «Отправить». Время сервера.')
                    ->seconds(false),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('title')->label('Название')->searchable()->wrap(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Статус')
                    ->colors(['gray' => Broadcast::DRAFT, 'warning' => Broadcast::SCHEDULED, 'info' => Broadcast::SENDING, 'success' => Broadcast::SENT])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Broadcast::DRAFT     => 'Черновик',
                        Broadcast::SCHEDULED => 'Запланирована',
                        Broadcast::SENDING   => 'Отправляется',
                        Broadcast::SENT      => 'Отправлена',
                        default              => $state,
                    }),
                Tables\Columns\TextColumn::make('scheduled_at')->label('Когда')->dateTime('d.m.Y H:i')->placeholder('сразу'),
                Tables\Columns\TextColumn::make('recipients_count')->label('Доставлено')->placeholder('—'),
                Tables\Columns\TextColumn::make('failed_count')->label('Не доставлено')->placeholder('—'),
                Tables\Columns\TextColumn::make('sent_at')->label('Отправлена')->dateTime('d.m.Y H:i')->placeholder('—'),
            ])
            ->actions([
                Action::make('test')
                    ->label('Тест себе')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('gray')
                    ->visible(fn (Broadcast $record): bool => $record->isEditable())
                    ->modalHeading('Пробная отправка')
                    ->modalDescription('Сообщение придёт только на этот Telegram ID. Свой ID можно узнать у бота @userinfobot. Человек должен хотя бы раз нажать /start в нашем боте.')
                    ->form([TextInput::make('telegram_id')->label('Telegram ID')->numeric()->required()])
                    ->action(function (Broadcast $record, array $data): void {
                        try {
                            app(BroadcastMessenger::class)->send((int) $data['telegram_id'], $record);
                            Notification::make()->title('Тест отправлен')->success()->send();
                        } catch (\Throwable $e) {
                            Notification::make()->title('Не удалось отправить')->body(mb_substr($e->getMessage(), 0, 300))->danger()->send();
                        }
                    }),

                Action::make('send')
                    ->label('Отправить')
                    ->icon('heroicon-o-rocket-launch')
                    ->color('success')
                    ->visible(fn (Broadcast $record): bool => $record->status === Broadcast::DRAFT)
                    ->requiresConfirmation()
                    ->modalHeading('Отправить рассылку?')
                    ->modalDescription(fn (Broadcast $record): string => 'Получат: '.$record->recipients()->count().' чел. '.($record->scheduled_at && $record->scheduled_at->isFuture() ? 'Уйдёт '.$record->scheduled_at->format('d.m.Y H:i').'.' : 'Отправка начнётся в течение минуты.').' Отменить после начала отправки нельзя.')
                    ->action(function (Broadcast $record): void {
                        $record->update(['status' => Broadcast::SCHEDULED]);
                        Notification::make()->title('Рассылка поставлена в очередь')->success()->send();
                    }),

                Action::make('unschedule')
                    ->label('Вернуть в черновик')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    ->visible(fn (Broadcast $record): bool => $record->status === Broadcast::SCHEDULED)
                    ->requiresConfirmation()
                    ->action(fn (Broadcast $record) => $record->update(['status' => Broadcast::DRAFT])),

                Action::make('duplicate')
                    ->label('Копия')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('gray')
                    ->action(function (Broadcast $record) {
                        $copy = Broadcast::create($record->only(['body', 'image_path', 'buttons', 'audience']) + ['title' => $record->title.' (копия)', 'status' => Broadcast::DRAFT]);

                        return redirect(static::getUrl('edit', ['record' => $copy]));
                    }),

                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListBroadcasts::route('/'),
            'create' => Pages\CreateBroadcast::route('/create'),
            'edit'   => Pages\EditBroadcast::route('/{record}/edit'),
        ];
    }
}
