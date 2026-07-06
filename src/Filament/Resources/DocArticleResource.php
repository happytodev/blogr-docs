<?php

namespace Happytodev\BlogrDocs\Filament\Resources;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

use Filament\Resources\Resource;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Actions\Action;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;

use Filament\Tables\Table;
use Happytodev\BlogrDocs\Models\DocArticle;
use Happytodev\BlogrDocs\Models\DocArticleTranslation;

class DocArticleResource extends Resource
{
    protected static ?string $model = DocArticle::class;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-document-text';
    }

    public static function getNavigationSort(): ?int
    {
        return 1;
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Docs';
    }

    public static function getModelLabel(): string
    {
        return 'Article';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Articles';
    }

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Article')
                    ->columnSpanFull()
                    ->schema([
                        Select::make('parent_id')
                            ->label('Parent')
                            ->relationship('parent', 'id')
                            ->getOptionLabelFromRecordUsing(function (DocArticle $record) {
                                $translation = $record->defaultTranslation();

                                return $translation ? $translation->title : "#{$record->id}";
                            })
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->columnSpan(1),

                        TextInput::make('icon')
                            ->label('Icon (heroicon name)')
                            ->helperText('e.g. book-open, academic-cap, code-bracket')
                            ->nullable()
                            ->columnSpan(1),

                        Toggle::make('is_published')
                            ->default(false)
                            ->columnSpan(1),

                        Toggle::make('display_toc')
                            ->label('Display table of contents')
                            ->default(true),
                    ])
                    ->columns(2),

                Section::make('Translations')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('translations')
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
                                    ->unique(DocArticleTranslation::class, 'slug', ignoreRecord: true)
                                    ->columnSpan(1),

                                Textarea::make('excerpt')
                                    ->rows(2)
                                    ->columnSpan(1),

                                \Filament\Forms\Components\MarkdownEditor::make('content')
                                    ->fileAttachmentsDirectory('docs')
                                    ->required()
                                    ->columnSpanFull(),

                                TextInput::make('seo_title')
                                    ->maxLength(255)
                                    ->columnSpan(1),

                                Textarea::make('seo_description')
                                    ->rows(2)
                                    ->columnSpan(1),

                                TextInput::make('seo_keywords')
                                    ->columnSpan(2),
                            ])
                            ->columns(2)
                            ->defaultItems(1)
                            ->addActionLabel('Add translation')
                            ->collapsible()
                            ->cloneable()
                            ->itemLabel(function (array $state): ?string {
                                $locale = $state['locale'] ?? '?';

                                return '['.strtoupper($locale).'] '.($state['title'] ?? 'New translation');
                            })
                            ->reorderable(false),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('title')
                    ->label('Title')
                    ->getStateUsing(function (DocArticle $record): string {
                        $translation = $record->defaultTranslation();
                        return $translation ? $translation->title : '';
                    }),

                TextColumn::make('slug')
                    ->label('Slug')
                    ->getStateUsing(function (DocArticle $record): string {
                        $translation = $record->defaultTranslation();
                        return $translation ? $translation->slug : '';
                    }),

                TextColumn::make('parent_id')
                    ->label('Parent')
                    ->getStateUsing(function (DocArticle $record): string {
                        if (! $record->parent_id) {
                            return '-';
                        }
                        $parent = $record->parent;
                        if (! $parent) {
                            return '-';
                        }
                        $translation = $parent->defaultTranslation();

                        return $translation ? $translation->title : "#{$parent->id}";
                    })
                    ->searchable(query: function ($query, $search) {
                        return $query->whereHas('parent.translations', fn ($q) => $q->where('title', 'like', "%{$search}%"));
                    }),

                TextColumn::make('position')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('is_published')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('change_parent')
                    ->label('Parent')
                    ->icon('heroicon-o-folder-arrow-down')
                    ->form([
                        \Filament\Forms\Components\Select::make('parent_id')
                            ->label('New parent')
                            ->relationship('parent', 'id')
                            ->getOptionLabelFromRecordUsing(function (DocArticle $record) {
                                $translation = $record->defaultTranslation();
                                return $translation ? $translation->title : "#{$record->id}";
                            })
                            ->searchable()
                            ->preload()
                            ->nullable(),
                    ])
                    ->action(function (array $data, DocArticle $record) {
                        $record->update(['parent_id' => $data['parent_id']]);
                        \Filament\Notifications\Notification::make()
                            ->title('Parent updated')
                            ->success()
                            ->send();
                    }),
                EditAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ])
            ->defaultSort('position')
            ->reorderable('position');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => \Happytodev\BlogrDocs\Filament\Resources\Pages\ListDocArticles::route('/'),
            'create' => \Happytodev\BlogrDocs\Filament\Resources\Pages\CreateDocArticle::route('/create'),
            'edit' => \Happytodev\BlogrDocs\Filament\Resources\Pages\EditDocArticle::route('/{record}/edit'),
        ];
    }
}
