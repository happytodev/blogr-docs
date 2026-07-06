<?php

namespace Happytodev\BlogrDocs\Filament\Pages;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Slider;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Livewire\WithFileUploads;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DocsSettings extends Page
{
    use WithFileUploads;

    protected string $view = 'blogr-docs::docs-settings';

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-cog-6-tooth';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Docs';
    }

    public static function getNavigationSort(): ?int
    {
        return 5;
    }

    // General
    public bool $enabled = true;
    public string $prefix = 'docs';

    // Sidebar
    public bool $sidebarCollapsible = true;
    public bool $sidebarShowIcons = true;

    // TOC
    public bool $tocEnabled = true;
    public int $tocMaxLevel = 3;

    // Search
    public bool $searchEnabled = true;
    public int $searchMinLength = 2;
    public int $searchMaxResults = 20;

    // PDF
    public bool $pdfEnabled = false;
    public string $pdfDriver = 'dompdf';
    public string $pdfPageSize = 'A4';
    public string $pdfOrientation = 'portrait';

    public bool $pdfWatermarkEnabled = false;
    public string $pdfWatermarkText = 'Confidential';
    public array $pdfWatermarkImage = [];
    public $pdfWatermarkOpacity = 0.2;
    public string $pdfWatermarkPosition = 'center';
    public int $pdfWatermarkRotation = -45;
    public int $pdfWatermarkSize = 60;

    // Embeds
    public bool $embedYoutube = true;
    public bool $embedVimeo = true;
    public bool $embedDailymotion = true;
    public bool $embedSpotify = true;
    public bool $embedSoundcloud = true;
    public bool $embedDeezer = true;
    public bool $embedApplePodcasts = true;

    public function mount(): void
    {
        $this->enabled = config('blogr-docs.enabled', true);
        $this->prefix = config('blogr-docs.prefix', 'docs');
        $this->sidebarCollapsible = config('blogr-docs.sidebar.collapsible', true);
        $this->sidebarShowIcons = config('blogr-docs.sidebar.show_icons', true);
        $this->tocEnabled = config('blogr-docs.toc.enabled', true);
        $this->tocMaxLevel = config('blogr-docs.toc.max_level', 3);
        $this->searchEnabled = config('blogr-docs.search.enabled', true);
        $this->searchMinLength = config('blogr-docs.search.min_length', 2);
        $this->searchMaxResults = config('blogr-docs.search.max_results', 20);
        $this->pdfEnabled = config('blogr-docs.pdf.enabled', false);
        $this->pdfDriver = config('blogr-docs.pdf.driver', 'dompdf');
        $this->pdfPageSize = config('blogr-docs.pdf.page_size', 'A4');
        $this->pdfOrientation = config('blogr-docs.pdf.orientation', 'portrait');
        $this->pdfWatermarkEnabled = config('blogr-docs.pdf.watermark.enabled', false);
        $this->pdfWatermarkText = config('blogr-docs.pdf.watermark.text', 'Confidential');
        $img = config('blogr-docs.pdf.watermark.image');
        if ($img) {
            $fullPath = str_contains($img, '/') ? $img : 'docs/pdf-watermarks/' . $img;
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($fullPath)) {
                $this->pdfWatermarkImage = [$fullPath];
            } else {
                $this->pdfWatermarkImage = [];
            }
        } else {
            $this->pdfWatermarkImage = [];
        }
        $this->pdfWatermarkOpacity = config('blogr-docs.pdf.watermark.opacity', 0.2);
        $this->pdfWatermarkPosition = config('blogr-docs.pdf.watermark.position', 'center');
        $this->pdfWatermarkRotation = config('blogr-docs.pdf.watermark.rotation', -45);
        $this->pdfWatermarkSize = config('blogr-docs.pdf.watermark.size', 60);
        $this->embedYoutube = config('blogr-docs.embeds.youtube', true);
        $this->embedVimeo = config('blogr-docs.embeds.vimeo', true);
        $this->embedDailymotion = config('blogr-docs.embeds.dailymotion', true);
        $this->embedSpotify = config('blogr-docs.embeds.spotify', true);
        $this->embedSoundcloud = config('blogr-docs.embeds.soundcloud', true);
        $this->embedDeezer = config('blogr-docs.embeds.deezer', true);
        $this->embedApplePodcasts = config('blogr-docs.embeds.apple_podcasts', true);
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('General')
                    ->columnSpanFull()
                    ->schema([
                        Toggle::make('enabled')
                            ->label('Enable documentation system')
                            ->helperText('When disabled, all docs routes return 404')
                            ->columnSpan(1),

                        TextInput::make('prefix')
                            ->label('URL prefix')
                            ->helperText('e.g. "docs" → /docs, /docs/article')
                            ->required()
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                Section::make('Sidebar')
                    ->columnSpanFull()
                    ->schema([
                        Toggle::make('sidebarCollapsible')
                            ->label('Collapsible sections')
                            ->columnSpan(1),

                        Toggle::make('sidebarShowIcons')
                            ->label('Show icons')
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                Section::make('Table of Contents')
                    ->columnSpanFull()
                    ->schema([
                        Toggle::make('tocEnabled')
                            ->label('Enable table of contents')
                            ->helperText('Auto-generated from headings')
                            ->live()
                            ->columnSpan(1),

                        Select::make('tocMaxLevel')
                            ->label('Maximum heading level')
                            ->options([
                                2 => 'H2',
                                3 => 'H3',
                                4 => 'H4',
                                5 => 'H5',
                                6 => 'H6',
                            ])
                            ->visible(fn () => $this->tocEnabled)
                            ->required()
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                Section::make('Search')
                    ->columnSpanFull()
                    ->schema([
                        Toggle::make('searchEnabled')
                            ->label('Enable search')
                            ->live()
                            ->columnSpan(1),

                        TextInput::make('searchMinLength')
                            ->label('Minimum search length')
                            ->numeric()
                            ->minValue(1)
                            ->visible(fn () => $this->searchEnabled)
                            ->required()
                            ->columnSpan(1),

                        TextInput::make('searchMaxResults')
                            ->label('Max results')
                            ->numeric()
                            ->minValue(1)
                            ->visible(fn () => $this->searchEnabled)
                            ->required()
                            ->columnSpan(2),
                    ])
                    ->columns(2),

                Section::make('PDF Export')
                    ->columnSpanFull()
                    ->schema([
                        Toggle::make('pdfEnabled')
                            ->label('Enable PDF export')
                            ->live()
                            ->columnSpan(1),

                        Select::make('pdfDriver')
                            ->label('Driver')
                            ->options(['dompdf' => 'Dompdf'])
                            ->visible(fn () => $this->pdfEnabled)
                            ->required()
                            ->columnSpan(1),

                        Select::make('pdfPageSize')
                            ->label('Page size')
                            ->options(['A4' => 'A4', 'Letter' => 'Letter', 'Legal' => 'Legal', 'A3' => 'A3', 'A5' => 'A5'])
                            ->visible(fn () => $this->pdfEnabled)
                            ->required()
                            ->columnSpan(1),

                        ToggleButtons::make('pdfOrientation')
                            ->label('Orientation')
                            ->options(['portrait' => 'Portrait', 'landscape' => 'Landscape'])
                            ->inline()
                            ->visible(fn () => $this->pdfEnabled)
                            ->required()
                            ->columnSpan(1),

                        Toggle::make('pdfWatermarkEnabled')
                            ->label('Watermark')
                            ->helperText('Add a watermark text or image to PDF exports')
                            ->live()
                            ->visible(fn () => $this->pdfEnabled)
                            ->columnSpan(1),

                        TextInput::make('pdfWatermarkText')
                            ->label('Watermark text')
                            ->visible(fn () => $this->pdfEnabled && $this->pdfWatermarkEnabled)
                            ->columnSpan(1),

                        FileUpload::make('pdfWatermarkImage')
                            ->label('Watermark image')
                            ->image()
                            ->disk('public')
                            ->directory('docs/pdf-watermarks')
                            ->visible(fn () => $this->pdfEnabled && $this->pdfWatermarkEnabled)
                            ->columnSpan(1),

                        Slider::make('pdfWatermarkOpacity')
                            ->label('Opacity')
                            ->minValue(0.1)
                            ->maxValue(1.0)
                            ->step(0.1)
                            ->default(0.2)
                            ->live()
                            ->hint(fn ($state) => $state)
                            ->visible(fn () => $this->pdfEnabled && $this->pdfWatermarkEnabled)
                            ->columnSpan(1),

                        Select::make('pdfWatermarkPosition')
                            ->label('Position')
                            ->options([
                                'center' => 'Center',
                                'top-left' => 'Top left',
                                'top-center' => 'Top center',
                                'top-right' => 'Top right',
                                'center-left' => 'Center left',
                                'center-right' => 'Center right',
                                'bottom-left' => 'Bottom left',
                                'bottom-center' => 'Bottom center',
                                'bottom-right' => 'Bottom right',
                            ])
                            ->visible(fn () => $this->pdfEnabled && $this->pdfWatermarkEnabled)
                            ->columnSpan(1),

                        Slider::make('pdfWatermarkRotation')
                            ->label('Rotation')
                            ->minValue(-90)
                            ->maxValue(90)
                            ->step(5)
                            ->default(-45)
                            ->live()
                            ->hint(fn ($state) => $state.'°')
                            ->visible(fn () => $this->pdfEnabled && $this->pdfWatermarkEnabled)
                            ->columnSpan(1),

                        Slider::make('pdfWatermarkSize')
                            ->label('Size')
                            ->minValue(20)
                            ->maxValue(200)
                            ->step(10)
                            ->default(60)
                            ->live()
                            ->hint(fn ($state) => $state.'px')
                            ->visible(fn () => $this->pdfEnabled && $this->pdfWatermarkEnabled)
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                Section::make('Embedded Media')
                    ->columnSpanFull()
                    ->schema([
                        Toggle::make('embedYoutube')->label('YouTube'),
                        Toggle::make('embedVimeo')->label('Vimeo'),
                        Toggle::make('embedDailymotion')->label('Dailymotion'),
                        Toggle::make('embedSpotify')->label('Spotify'),
                        Toggle::make('embedSoundcloud')->label('SoundCloud'),
                        Toggle::make('embedDeezer')->label('Deezer'),
                        Toggle::make('embedApplePodcasts')->label('Apple Podcasts'),
                    ])
                    ->columns(3),
            ]);
    }

    protected function getFormActions(): array
    {
        return [];
    }

    public function save(): void
    {
        $this->validate();

        $path = __DIR__.'/../../../config/blogr-docs.php';
        if (! file_exists($path)) {
            $path = config_path('blogr-docs.php');
        }

        if (! file_exists($path)) {
            Notification::make()->title('Config file not found')->danger()->send();
            return;
        }

        $config = require $path;

        // Persist uploaded file manually (Page doesn't auto-dehydrate FileUpload)
        $watermarkImage = null;
        if (! empty($this->pdfWatermarkImage)) {
            $file = reset($this->pdfWatermarkImage);
            if (is_object($file) && method_exists($file, 'store')) {
                \Illuminate\Support\Facades\Storage::disk('public')->makeDirectory('docs/pdf-watermarks');
                $watermarkImage = $file->store('docs/pdf-watermarks', 'public');
            } elseif (is_string($file) && ! str_contains($file, 'livewire-tmp')) {
                $watermarkImage = $file;
            }
        }

        $config['enabled'] = $this->enabled;
        $config['prefix'] = $this->prefix;
        $config['sidebar']['collapsible'] = $this->sidebarCollapsible;
        $config['sidebar']['show_icons'] = $this->sidebarShowIcons;
        $config['toc']['enabled'] = $this->tocEnabled;
        $config['toc']['max_level'] = $this->tocMaxLevel;
        $config['search']['enabled'] = $this->searchEnabled;
        $config['search']['min_length'] = $this->searchMinLength;
        $config['search']['max_results'] = $this->searchMaxResults;
        $config['pdf']['enabled'] = $this->pdfEnabled;
        $config['pdf']['driver'] = $this->pdfDriver;
        $config['pdf']['page_size'] = $this->pdfPageSize;
        $config['pdf']['orientation'] = $this->pdfOrientation;
        $config['pdf']['watermark']['enabled'] = $this->pdfWatermarkEnabled;
        $config['pdf']['watermark']['text'] = $this->pdfWatermarkText;
        $config['pdf']['watermark']['image'] = $watermarkImage ?: null;
        $config['pdf']['watermark']['opacity'] = (float) $this->pdfWatermarkOpacity;
        $config['pdf']['watermark']['position'] = $this->pdfWatermarkPosition;
        $config['pdf']['watermark']['rotation'] = (int) $this->pdfWatermarkRotation;
        $config['pdf']['watermark']['size'] = (int) $this->pdfWatermarkSize;
        $config['embeds']['youtube'] = $this->embedYoutube;
        $config['embeds']['vimeo'] = $this->embedVimeo;
        $config['embeds']['dailymotion'] = $this->embedDailymotion;
        $config['embeds']['spotify'] = $this->embedSpotify;
        $config['embeds']['soundcloud'] = $this->embedSoundcloud;
        $config['embeds']['deezer'] = $this->embedDeezer;
        $config['embeds']['apple_podcasts'] = $this->embedApplePodcasts;

        $written = '<?php'."\n\nreturn ".var_export($config, true).";\n";
        file_put_contents($path, $written);

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        Notification::make()
            ->title('Settings saved')
            ->success()
            ->send();
    }

    public static function getNavigationLabel(): string
    {
        return 'Settings';
    }

    public function getTitle(): string
    {
        return 'Docs Settings';
    }
}
