<?php

namespace Happytodev\BlogrDocs\Filament\Resources\Pages;

use Filament\Resources\Pages\CreateRecord;
use Happytodev\BlogrDocs\Filament\Resources\DocArticleResource;
use Happytodev\BlogrDocs\Models\DocArticle;

class CreateDocArticle extends CreateRecord
{
    protected static string $resource = DocArticleResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['position'] = DocArticle::max('position') + 1;

        return $data;
    }
}
