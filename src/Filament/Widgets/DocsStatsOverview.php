<?php

namespace Happytodev\BlogrDocs\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Happytodev\BlogrDocs\Models\DocArticle;

class DocsStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $total = DocArticle::count();
        $published = DocArticle::where('is_published', true)->count();
        $roots = DocArticle::whereNull('parent_id')->count();
        $leaves = DocArticle::whereDoesntHave('children')->count();

        return [
            Stat::make('Total Articles', $total)
                ->description('All documentation articles'),

            Stat::make('Published', $published)
                ->description('Visible on frontend'),

            Stat::make('Sections', $roots)
                ->description('Root-level sections'),

            Stat::make('Pages', $leaves)
                ->description('Leaf articles with content'),
        ];
    }
}
