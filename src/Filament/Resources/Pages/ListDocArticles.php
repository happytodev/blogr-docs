<?php

namespace Happytodev\BlogrDocs\Filament\Resources\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Happytodev\BlogrDocs\Filament\Resources\DocArticleResource;

class ListDocArticles extends ListRecords
{
    protected static string $resource = DocArticleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
