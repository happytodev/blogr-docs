<?php

namespace Happytodev\BlogrDocs\Filament\Pages;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DocsSettings extends Page
{
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

    public bool $pdfEnabled = false;

    public string $pdfDriver = 'dompdf';

    public string $pdfPageSize = 'A4';

    public string $pdfOrientation = 'portrait';

    public function mount(): void
    {
        $this->pdfEnabled = config('blogr-docs.pdf.enabled', false);
        $this->pdfDriver = config('blogr-docs.pdf.driver', 'dompdf');
        $this->pdfPageSize = config('blogr-docs.pdf.page_size', 'A4');
        $this->pdfOrientation = config('blogr-docs.pdf.orientation', 'portrait');
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('PDF Export')
                    ->columnSpanFull()
                    ->schema([
                        Toggle::make('pdfEnabled')
                            ->label('Enable PDF export')
                            ->helperText('Allow users to download documentation articles as PDF')
                            ->live(),

                        Select::make('pdfDriver')
                            ->label('PDF driver')
                            ->options([
                                'dompdf' => 'Dompdf',
                            ])
                            ->visible(fn () => $this->pdfEnabled)
                            ->required(),

                        Select::make('pdfPageSize')
                            ->label('Page size')
                            ->options([
                                'A4' => 'A4',
                                'Letter' => 'Letter',
                                'Legal' => 'Legal',
                                'A3' => 'A3',
                                'A5' => 'A5',
                            ])
                            ->visible(fn () => $this->pdfEnabled)
                            ->required(),

                        ToggleButtons::make('pdfOrientation')
                            ->label('Orientation')
                            ->options([
                                'portrait' => 'Portrait',
                                'landscape' => 'Landscape',
                            ])
                            ->inline()
                            ->visible(fn () => $this->pdfEnabled)
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }

    protected function getFormActions(): array
    {
        return [];
    }

    public function save(): void
    {
        $this->validate();

        $path = config_path('blogr-docs.php');
        $config = require $path;

        $config['pdf']['enabled'] = $this->pdfEnabled;
        $config['pdf']['driver'] = $this->pdfDriver;
        $config['pdf']['page_size'] = $this->pdfPageSize;
        $config['pdf']['orientation'] = $this->pdfOrientation;

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
