<?php

namespace Happytodev\BlogrDocs\Filament\Resources\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Happytodev\BlogrDocs\Filament\Resources\DocLearningPathResource;

class ListLearningPaths extends ListRecords
{
    protected static string $resource = DocLearningPathResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
