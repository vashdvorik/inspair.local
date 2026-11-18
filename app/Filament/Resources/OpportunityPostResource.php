<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\OpportunityPostResource\Pages;
use App\Models\OpportunityPost;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

/** Возможности (поездки и курсы за рубежом): администратор добавляет их здесь, они сразу появляются на сайте и в кабинете. */
class OpportunityPostResource extends Resource
{
    protected static ?string $model = OpportunityPost::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-globe-europe-africa';

    protected static ?string $navigationLabel = 'Возможности';

    protected static ?string $modelLabel = 'Возможность';

    protected static ?string $pluralModelLabel = 'Возможности';

    protected static ?int $navigationSort = 2;

    /** Список строк в одном поле: каждая строка отдельный пункт. */
    private static function lines(Textarea $field): Textarea
    {
        return $field
            ->rows(5)
            ->helperText('Каждый пункт с новой строки.')
            ->formatStateUsing(fn ($state): string => is_array($state) ? implode("\n", $state) : (string) $state)
            ->dehydrateStateUsing(fn ($state): array => array_values(array_filter(array_map('trim', explode("\n", (string) $state)), fn ($l) => $l !== '')));
    }

    private static function languageTab(string $l, string $title): Tab
    {
        $en = $l === 'en';

        return Tab::make($title)->schema([
            TextInput::make("title.{$l}")
                ->label('Название на этом языке')
                ->helperText('Пусто: показывается оригинальное название.')
                ->maxLength(200)
                ->columnSpanFull(),
            TextInput::make("country.{$l}")
                ->label('Страна')
                ->required($en)
                ->maxLength(80),
            Textarea::make("text.{$l}")
                ->label('Коротко для карточки')
                ->helperText('2–3 предложения: что это за курс или поездка.')
                ->rows(3)
                ->required($en)
                ->maxLength(400)
                ->columnSpanFull(),
            Textarea::make("detail.{$l}.lead")
                ->label('Вступление на странице')
                ->rows(3)
                ->columnSpanFull(),
            self::lines(Textarea::make("detail.{$l}.explore"))->label('Что изучают участники')->columnSpanFull(),
            TextInput::make("detail.{$l}.format")
                ->label('Формат')
                ->helperText('Продолжает фразу «Проект сочетает …»: например, «практические упражнения, обмен опытом».')
                ->columnSpanFull(),
            self::lines(Textarea::make("detail.{$l}.benefits"))->label('Что даст участие')->columnSpanFull(),
            Textarea::make("detail.{$l}.audience")->label('Для кого')->rows(2)->columnSpanFull(),
            TextInput::make("detail.{$l}.note")->label('Примечание (необязательно)')->columnSpanFull(),
        ])->columns(1);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Section::make('Основное')->schema([
                TextInput::make('name')
                    ->label('Название')
                    ->helperText('Как в оригинале, обычно на английском.')
                    ->required()
                    ->maxLength(200)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, \Filament\Schemas\Components\Utilities\Set $set, \Filament\Schemas\Components\Utilities\Get $get): void {
                        if (blank($get('slug'))) {
                            $set('slug', Str::slug((string) $state));
                        }
                    }),
                TextInput::make('slug')
                    ->label('Адрес страницы')
                    ->helperText('Латиницей, например emotion-armenia. Заполняется сам.')
                    ->required()
                    ->regex('/^[a-z0-9\-]+$/')
                    ->unique(ignoreRecord: true)
                    ->maxLength(120),
                TextInput::make('flag')
                    ->label('Флаг страны')
                    ->placeholder('🇦🇲')
                    ->maxLength(16),
                DatePicker::make('start_date')->label('Начало'),
                DatePicker::make('end_date')->label('Конец'),
                DatePicker::make('deadline')->label('Срок подачи заявок')->helperText('Пусто: срок не указывается.'),
                DatePicker::make('posted_on')->label('Дата публикации')->default(now())->required()->helperText('По ней лента сортируется: новые сверху.'),
                TextInput::make('source_url')->label('Ссылка на источник')->url()->placeholder('https://')->helperText('Например, пост в Instagram. Участникам не показывается.'),
                Toggle::make('published')->label('Показывать на сайте и в кабинете')->default(true),
                Toggle::make('notify_bot')->label('Разослать участникам в Telegram-бот')->helperText('Бот пришлёт карточку всем, кто не отключил уведомления (в течение 15 минут). Выключите, если рассылка не нужна.')->default(true)->visibleOn('create'),
            ])->columns(2),

            Section::make('Фото')->schema([
                FileUpload::make('image')
                    ->label('Фото города или страны')
                    ->helperText('Горизонтальное, лучше 1200×800. Загружается и сжимается до 900 px по ширине. Фото должно быть свободным для использования.')
                    ->image()
                    ->disk('opportunity_images')
                    ->visibility('public')
                    ->imageResizeMode('contain')
                    ->imageResizeTargetWidth('900')
                    ->imageResizeUpscale(false)
                    ->maxSize(8192)
                    ->required()
                    ->getUploadedFileNameForStorageUsing(fn (\Livewire\Features\SupportFileUploads\TemporaryUploadedFile $file): string => Str::lower(Str::random(10)).'.'.$file->getClientOriginalExtension()),
            ]),

            Section::make('Текст на трёх языках')
                ->description('Английский обязателен. Пустые русский и румынский заполнятся английским текстом, пока вы не допишете перевод.')
                ->schema([
                    Tabs::make('languages')->tabs([
                        self::languageTab('en', 'English'),
                        self::languageTab('ru', 'Русский'),
                        self::languageTab('ro', 'Română'),
                    ])->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('posted_on', 'desc')
            ->columns([
                Tables\Columns\ImageColumn::make('image')->label('')->disk('opportunity_images')->square()->size(54),
                Tables\Columns\TextColumn::make('name')->label('Название')->searchable()->wrap()->description(fn (OpportunityPost $r): string => trim(($r->flag ?? '').' '.($r->country['ru'] ?? $r->country['en'] ?? ''))),
                Tables\Columns\TextColumn::make('start_date')->label('Даты')->formatStateUsing(fn ($state, OpportunityPost $r): string => $r->start_date ? $r->start_date->format('d.m.Y').($r->end_date ? ' – '.$r->end_date->format('d.m.Y') : '') : '—'),
                Tables\Columns\TextColumn::make('deadline')->label('Срок заявок')->date('d.m.Y')->placeholder('—'),
                Tables\Columns\ToggleColumn::make('published')->label('На сайте'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListOpportunityPosts::route('/'),
            'create' => Pages\CreateOpportunityPost::route('/create'),
            'edit'   => Pages\EditOpportunityPost::route('/{record}/edit'),
        ];
    }
}
