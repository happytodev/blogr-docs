<?php

namespace Happytodev\BlogrDocs\Helpers;

use Happytodev\BlogrDocs\Models\DocArticle;
use Illuminate\Support\Collection;

class DocTreeHelper
{
    /**
     * Build a nested tree from all published articles.
     */
    public function buildTree(?string $locale = null): Collection
    {
        $articles = DocArticle::with(['translations', 'children'])
            ->published()
            ->ordered()
            ->get();

        return $this->buildChildren($articles, null, $locale);
    }

    /**
     * Build children for a given parent ID.
     */
    private function buildChildren(Collection $articles, ?int $parentId, ?string $locale): Collection
    {
        return $articles
            ->where('parent_id', $parentId)
            ->map(function (DocArticle $article) use ($articles, $locale) {
                $children = $this->buildChildren($articles, $article->id, $locale);

                return [
                    'article' => $article,
                    'translation' => $locale
                        ? ($article->translation($locale) ?? $article->defaultTranslation())
                        : $article->defaultTranslation(),
                    'children' => $children,
                    'has_children' => $children->isNotEmpty(),
                ];
            })
            ->values();
    }

    /**
     * Resolve an article by its slug path segments.
     *
     * @param  array<string>  $segments
     */
    public function resolvePath(array $segments, string $locale): ?DocArticle
    {
        if (empty($segments)) {
            return null;
        }

        $parentId = null;
        $target = null;

        foreach ($segments as $slug) {
            $query = DocArticle::whereHas('translations', function ($q) use ($slug, $locale) {
                $q->where('slug', $slug)->where('locale', $locale);
            })->published();

            if ($parentId !== null) {
                $query->where('parent_id', $parentId);
            } else {
                $query->whereNull('parent_id');
            }

            $article = $query->first();

            if (! $article) {
                return null;
            }

            $target = $article;
            $parentId = $article->id;
        }

        return $target;
    }

    /**
     * Get previous sibling (ordered by position).
     */
    public function getPrevArticle(DocArticle $article): ?DocArticle
    {
        return DocArticle::where('parent_id', $article->parent_id)
            ->where('position', '<', $article->position)
            ->published()
            ->orderBy('position', 'desc')
            ->first();
    }

    /**
     * Get next sibling (ordered by position).
     */
    public function getNextArticle(DocArticle $article): ?DocArticle
    {
        return DocArticle::where('parent_id', $article->parent_id)
            ->where('position', '>', $article->position)
            ->published()
            ->ordered()
            ->first();
    }

    /**
     * Build a descendant tree rooted at the given article, including headings.
     */
    public function getDescendantTree(DocArticle $article, string $locale): Collection
    {
        $allPublished = DocArticle::with(['translations' => function ($q) use ($locale) {
            $q->where('locale', $locale);
        }])->published()->ordered()->get();

        return $this->buildDescendants($allPublished, $article->id, $locale);
    }

    /**
     * Recursively build descendant nodes from a flat collection.
     */
    private function buildDescendants(Collection $articles, int $parentId, string $locale): Collection
    {
        return $articles
            ->where('parent_id', $parentId)
            ->map(function (DocArticle $article) use ($articles, $locale) {
                $translation = $article->translation($locale) ?? $article->defaultTranslation();

                return [
                    'article' => $article,
                    'translation' => $translation,
                    'headings' => $translation?->headings ?? [],
                    'children' => $this->buildDescendants($articles, $article->id, $locale),
                    'has_children' => $articles->where('parent_id', $article->id)->isNotEmpty(),
                ];
            })
            ->values();
    }

    /**
     * Flatten the tree into a single-level list (useful for search).
     */
    public function flattenTree(?string $locale = null): Collection
    {
        $articles = DocArticle::with('translations')
            ->published()
            ->ordered()
            ->get();

        return $articles->map(function (DocArticle $article) use ($locale) {
            return [
                'article' => $article,
                'translation' => $locale
                    ? ($article->translation($locale) ?? $article->defaultTranslation())
                    : $article->defaultTranslation(),
            ];
        });
    }
}
