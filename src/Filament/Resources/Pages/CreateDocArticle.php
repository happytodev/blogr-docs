<?php

namespace Happytodev\BlogrDocs\Filament\Resources\Pages;

use Filament\Resources\Pages\CreateRecord;
use Happytodev\BlogrDocs\Filament\Resources\DocArticleResource;

class CreateDocArticle extends CreateRecord
{
    protected static string $resource = DocArticleResource::class;
}
