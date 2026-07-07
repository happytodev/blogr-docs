<?php

namespace Happytodev\BlogrDocs\Http\Controllers;

use Happytodev\BlogrDocs\Helpers\DocTreeHelper;
use Happytodev\BlogrDocs\Models\DocArticle;
use Happytodev\BlogrDocs\Models\DocArticleTranslation;
use Happytodev\BlogrDocs\Models\DocLearningPath;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;

class DocController extends Controller
{
    public function __construct(
        private DocTreeHelper $treeHelper
    ) {}

    public function index(Request $request, ?string $locale = null): mixed
    {
        $currentLocale = $locale ?? config('app.locale', 'en');
        $query = $request->get('q');

        if ($query) {
            $articleIds = DocArticleTranslation::where('locale', $currentLocale)
                ->where(function ($q) use ($query) {
                    $q->where('title', 'like', "%{$query}%")
                      ->orWhere('content', 'like', "%{$query}%")
                      ->orWhere('excerpt', 'like', "%{$query}%")
                      ->orWhere('slug', 'like', "%{$query}%");
                })
                ->pluck('doc_article_id');

            $articles = DocArticle::with('translations')
                ->whereIn('id', $articleIds)
                ->where('is_published', true)
                ->ordered()
                ->get();

            $localesEnabled = config('blogr.locales.enabled', false);
            $prefix = config('blogr-docs.prefix', 'docs');

            $results = $articles->map(function ($article) use ($currentLocale, $localesEnabled, $prefix) {
                $translation = $article->translation($currentLocale) ?? $article->defaultTranslation();
                if (! $translation) return null;

                $segments = [];
                $parent = $article->parent;
                while ($parent) {
                    $pt = $parent->translation($currentLocale) ?? $parent->defaultTranslation();
                    if ($pt) array_unshift($segments, $pt->slug);
                    $parent = $parent->parent;
                }
                $segments[] = $translation->slug;
                $path = implode('/', $segments);

                $url = $localesEnabled
                    ? route('blogr-docs.show', ['locale' => $currentLocale, 'path' => $path])
                    : route('blogr-docs.show', ['path' => $path]);

                return [
                    'title' => $translation->title,
                    'excerpt' => $translation->excerpt,
                    'url' => $url,
                ];
            })->filter()->values();

            $seoData = [
                'title' => __('blogr-docs::ui.search_results', ['query' => $query]),
                'description' => null,
            ];

            return view('blogr-docs::search', [
                'results' => $results,
                'query' => $query,
                'locale' => $currentLocale,
                'seoTitle' => $seoData['title'],
                'seoDescription' => $seoData['description'],
                'canonicalUrl' => url('/'.$prefix.'?q='.urlencode($query)),
            ]);
        }

        $tree = $this->treeHelper->buildTree($currentLocale);

        $learningPaths = DocLearningPath::with('translations')
            ->published()
            ->ordered()
            ->get()
            ->map(function ($path) use ($currentLocale) {
                return $path->translation($currentLocale) ?? $path->translations()->first();
            })
            ->filter();

        $seoData = [
            'title' => config('blogr-docs.seo.default_title', 'Documentation'),
            'description' => config('blogr-docs.seo.default_description'),
        ];

        return view('blogr-docs::index', [
            'tree' => $tree,
            'learningPaths' => $learningPaths,
            'locale' => $currentLocale,
            'seoTitle' => $seoData['title'],
            'seoDescription' => $seoData['description'],
            'canonicalUrl' => url('/'.config('blogr-docs.prefix', 'docs')),
        ]);
    }

    public function show(Request $request, string $path): mixed
    {
        return $this->renderArticle($path, config('app.locale', 'en'));
    }

    public function showLocalized(Request $request, string $locale, string $path): mixed
    {
        return $this->renderArticle($path, $locale);
    }

    private function renderArticle(string $path, string $locale): mixed
    {
        $segments = explode('/', $path);
        $localesEnabled = config('blogr.locales.enabled', false);

        // Check if first segment is a learning path slug
        $learningPath = DocLearningPath::whereHas('translations', function ($q) use ($segments, $locale) {
            $q->where('slug', $segments[0])->where('locale', $locale);
        })->first();

        if ($learningPath) {
            array_shift($segments);
        }

        $article = $this->treeHelper->resolvePath($segments, $locale);

        // Fallback: try to find article by last slug segment across all locales
        if (! $article && count($segments) > 0) {
            $lastSlug = end($segments);
            $translationInAnyLocale = DocArticleTranslation::where('slug', $lastSlug)->first();
            if ($translationInAnyLocale) {
                $article = $translationInAnyLocale->article;
            }
        }

        if (! $article) {
            abort(404);
        }

        $article->load('translations');

        // Find translation for requested locale, fall back to default
        $translation = $article->translation($locale);
        $displayLocale = $locale;

        if (! $translation) {
            $translation = $article->defaultTranslation();
            $displayLocale = $translation?->locale ?? $locale;
        }

        if (! $translation) {
            abort(404);
        }

        $isFallback = $displayLocale !== $locale;

        // Build available translations links
        $availableTranslations = $article->translations
            ? $article->translations->map(function ($t) use ($localesEnabled, $segments) {
                $path = implode('/', $segments);
                $url = $localesEnabled
                    ? route('blogr-docs.show', ['locale' => $t->locale, 'path' => $path])
                    : route('blogr-docs.show', ['path' => $path]);

                return [
                    'locale' => $t->locale,
                    'title' => $t->title,
                    'url' => $url,
                ];
            })
            : collect();

        $htmlContent = null;
        $tocHtml = null;

        if ($translation->content) {
            $converter = app('blogr-docs.converter');
            $htmlContent = $converter->convert($translation->content)->getContent();

            if (config('blogr-docs.toc.enabled', true)) {
                $htmlContent = $this->injectHeadingIds($htmlContent);
            }

            if (config('blogr-docs.toc.enabled', true) && $article->display_toc) {
                $tocHtml = $this->extractToc($htmlContent);
            }
        }

        $tree = $this->treeHelper->buildTree($displayLocale);
        $breadcrumbs = $article->getBreadcrumbs($displayLocale);
        $prevArticle = $this->treeHelper->getPrevArticle($article);
        $nextArticle = $this->treeHelper->getNextArticle($article);

        $prevTranslation = $prevArticle
            ? ($prevArticle->translation($displayLocale) ?? $prevArticle->defaultTranslation())
            : null;

        $nextTranslation = $nextArticle
            ? ($nextArticle->translation($displayLocale) ?? $nextArticle->defaultTranslation())
            : null;

        if ($learningPath) {
            $learningPathTranslation = $learningPath->translation($displayLocale) ?? $learningPath->translations()->first();
            array_unshift($breadcrumbs, [
                'title' => $learningPathTranslation?->title ?? 'Learning Path',
                'url' => $learningPathTranslation?->url() ?? '#',
            ]);
        }

        $seoData = [
            'title' => $translation->seo_title ?? $translation->title,
            'description' => $translation->seo_description ?? $translation->excerpt,
            'keywords' => $translation->seo_keywords,
        ];

        $pathForUrl = implode('/', $segments);
        $pdfUrl = $localesEnabled
            ? route('blogr-docs.pdf.localized', ['locale' => $displayLocale, 'path' => $pathForUrl])
            : route('blogr-docs.pdf', ['path' => $pathForUrl]);

        $descendantTree = null;
        if ($article->isOverview()) {
            $descendantTree = $this->treeHelper->getDescendantTree($article, $displayLocale);
        }

        return view('blogr-docs::show', [
            'article' => $article,
            'translation' => $translation,
            'htmlContent' => $htmlContent,
            'title' => $translation->title,
            'tree' => $tree,
            'descendantTree' => $descendantTree,
            'breadcrumbs' => $breadcrumbs,
            'prevArticle' => $prevArticle,
            'nextArticle' => $nextArticle,
            'prevTranslation' => $prevTranslation,
            'nextTranslation' => $nextTranslation,
            'locale' => $displayLocale,
            'requestedLocale' => $isFallback ? $locale : null,
            'isFallback' => $isFallback,
            'availableTranslations' => $availableTranslations,
            'seoTitle' => $seoData['title'],
            'seoDescription' => $seoData['description'],
            'seoKeywords' => $seoData['keywords'],
            'canonicalUrl' => $translation->url(),
            'pdfUrl' => $pdfUrl,
            'displayToc' => $article->display_toc,
            'tocHtml' => $tocHtml,
        ]);
    }

    public function downloadPdf(string $path): mixed
    {
        return $this->generatePdf($path, config('app.locale', 'en'));
    }

    public function downloadPdfLocalized(string $locale, string $path): mixed
    {
        return $this->generatePdf($path, $locale);
    }

    private function generatePdf(string $path, string $locale): mixed
    {
        if (! config('blogr-docs.pdf.enabled', false)) {
            abort(404);
        }

        $segments = explode('/', $path);
        $article = $this->treeHelper->resolvePath($segments, $locale);

        if (! $article) {
            $lastSlug = end($segments);
            $translationInAnyLocale = DocArticleTranslation::where('slug', $lastSlug)->first();
            if ($translationInAnyLocale) {
                $article = $translationInAnyLocale->article;
            }
        }

        if (! $article) {
            abort(404);
        }

        $translation = $article->translation($locale) ?? $article->defaultTranslation();

        if (! $translation) {
            abort(404);
        }

        $converter = app('blogr-docs.converter');
        $htmlContent = $converter->convert($translation->content ?? '')->getContent();

        $pdf = Pdf::loadHTML(view('blogr-docs::pdf', [
            'title' => $translation->title,
            'content' => $htmlContent,
            'locale' => $locale,
            'seoTitle' => $translation->seo_title ?? $translation->title,
            'seoDescription' => $translation->seo_description,
        ])->render());

        $pdf->setPaper(
            config('blogr-docs.pdf.page_size', 'A4'),
            config('blogr-docs.pdf.orientation', 'portrait')
        );

        return $pdf->download(Str::slug($translation->title).'.pdf');
    }

    private function extractToc(string $html): ?string
    {
        $maxLevel = config('blogr-docs.toc.max_level', 3);
        $levels = implode('', range(2, $maxLevel));

        preg_match_all(
            '/<h(['.$levels.'])\s.*?id="([^"]+)".*?>(.*?)<\/h\1>/i',
            $html,
            $matches,
            PREG_SET_ORDER
        );

        if (empty($matches)) {
            return null;
        }

        $toc = '<ul class="toc-list space-y-1 min-w-0">';
        $openLi = false;
        $openChildUl = false;

        foreach ($matches as $match) {
            $level = (int) $match[1];
            $id = $match[2];
            $text = preg_replace('/<a\b[^>]*>.*?<\/a>/i', '', $match[3]);
            $text = trim(strip_tags($text));
            $escaped = e($text);

            if ($level === 2) {
                if ($openChildUl) {
                    $toc .= '</ul>';
                    $openChildUl = false;
                }
                if ($openLi) {
                    $toc .= '</li>';
                }
                $toc .= '<li class="toc-level-2"><a href="#'.$id.'" class="truncate block" title="'.$escaped.'">'.$escaped.'</a>';
                $openLi = true;
            } elseif ($level === 3) {
                if ($openLi && ! $openChildUl) {
                    $toc .= '<ul class="toc-children space-y-1 mt-1 ml-2 border-l border-gray-200 dark:border-gray-700 pl-2">';
                    $openChildUl = true;
                }
                $toc .= '<li class="toc-level-3"><a href="#'.$id.'" class="truncate block" title="'.$escaped.'">'.$escaped.'</a></li>';
            }
        }

        if ($openChildUl) {
            $toc .= '</ul>';
        }
        if ($openLi) {
            $toc .= '</li>';
        }

        $toc .= '</ul>';

        return $toc;
    }

    private function injectHeadingIds(string $html): string
    {
        $maxLevel = config('blogr-docs.toc.max_level', 3);

        return preg_replace_callback(
            '/<h(['.implode('', range(2, $maxLevel)).'])(.*?)>(.*?)<\/h\1>/i',
            function ($matches) {
                $level = $matches[1];
                $attributes = $matches[2];
                $inner = $matches[3];

                if (preg_match('/id=["\']/i', $attributes)) {
                    return $matches[0];
                }

                $contentWithoutPermalink = preg_replace('/<a\b[^>]*>.*?<\/a>/i', '', $inner);
                $text = trim(strip_tags($contentWithoutPermalink));
                $id = Str::slug($text);

                $heading = '<h'.$level.$attributes.' id="'.$id.'" style="scroll-margin-top: 6rem">';
                $heading .= '<a href="#'.$id.'" class="heading-permalink" aria-hidden="true">#</a> ';
                $heading .= $inner;
                $heading .= '</h'.$level.'>';

                return $heading;
            },
            $html
        );
    }
}
