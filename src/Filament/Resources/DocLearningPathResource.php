<?php

namespace Happytodev\BlogrDocs\Filament\Resources;

use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Happytodev\BlogrDocs\Models\DocLearningPath;
use Happytodev\BlogrDocs\Models\DocLearningPathTranslation;

class DocLearningPathResource extends Resource
{
    protected static ?string $model = DocLearningPath::class;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-academic-cap';
    }

    public static function getNavigationSort(): ?int
    {
        return 2;
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Docs';
    }

    public static function getModelLabel(): string
    {
        return 'Learning Path';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Learning Paths';
    }

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Learning Path')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('position')
                            ->numeric()
                            ->default(0)
                            ->required()
                            ->columnSpan(1),

                        TextInput::make('icon')
                            ->label('Icon (heroicon name)')
                            ->nullable()
                            ->columnSpan(1),

                        Toggle::make('is_published')
                            ->default(false),
                    ])
                    ->columns(2),

                Section::make('Translations')
                    ->columnSpanFull()
                    ->schema([
                        \Filament\Forms\Components\Repeater::make('translations')
                            ->relationship('translations')
                            ->schema([
                                Select::make('locale')
                                    ->options(function () {
                                        return collect(config('blogr.locales.available', ['en']))
                                            ->mapWithKeys(fn ($locale) => [$locale => strtoupper($locale)]);
                                    })
                                    ->required()
                                    ->distinct()
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                    ->columnSpan(1),

                                TextInput::make('title')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($set, $get, ?string $state) {
                                        if ($state && ! $get('slug')) {
                                            $set('slug', \Illuminate\Support\Str::slug($state));
                                        }
                                    })
                                    ->columnSpan(1),

                                TextInput::make('slug')
                                    ->required()
                                    ->unique(DocLearningPathTranslation::class, 'slug', ignoreRecord: true)
                                    ->columnSpan(1),

                                Textarea::make('description')
                                    ->rows(3)
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->defaultItems(1)
                            ->addActionLabel('Add translation')
                            ->collapsible()
                            ->itemLabel(function (array $state): ?string {
                                $locale = $state['locale'] ?? '?';

                                return '['.strtoupper($locale).'] '.($state['title'] ?? 'New translation');
                            })
                            ->reorderable(false),
                    ]),

                Section::make('Articles')
                    ->columnSpanFull()
                    ->schema([
                        \Filament\Forms\Components\Select::make('articles')
                            ->relationship('articles', 'id')
                            ->getOptionLabelFromRecordUsing(function ($record) {
                                $translation = $record->defaultTranslation();
                                return $translation ? $translation->title : "#{$record->id}";
                            })
                            ->multiple()
                            ->preload()
                            ->searchable(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Title')
                    ->getStateUsing(function ($record): string {
                        $locale = config('app.locale', 'en');
                        $translation = $record->translation($locale) ?? $record->translations()->first();
                        return $translation ? $translation->title : '';
                    }),

                TextColumn::make('slug')
                    ->label('Slug')
                    ->getStateUsing(function ($record): string {
                        $locale = config('app.locale', 'en');
                        $translation = $record->translation($locale) ?? $record->translations()->first();
                        return $translation ? $translation->slug : '';
                    }),

                TextColumn::make('position')
                    ->sortable(),

                IconColumn::make('is_published')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ])
            ->defaultSort('position');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => \Happytodev\BlogrDocs\Filament\Resources\Pages\ListLearningPaths::route('/'),
            'create' => \Happytodev\BlogrDocs\Filament\Resources\Pages\CreateLearningPath::route('/create'),
            'edit' => \Happytodev\BlogrDocs\Filament\Resources\Pages\EditLearningPath::route('/{record}/edit'),
        ];
    }
}
