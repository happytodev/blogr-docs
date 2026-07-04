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
        if ($translation->content) {
            $converter = app('blogr-docs.converter');
            $htmlContent = $converter->convert($translation->content)->getContent();

            if (config('blogr-docs.toc.enabled', true) && $article->display_toc) {
                $htmlContent = $this->injectHeadingIds($htmlContent);
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

        return view('blogr-docs::show', [
            'article' => $article,
            'translation' => $translation,
            'htmlContent' => $htmlContent,
            'title' => $translation->title,
            'tree' => $tree,
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
            'displayToc' => $article->display_toc,
        ]);
    }

    public function downloadPdf(string $path): mixed
    {
        if (! config('blogr-docs.pdf.enabled', false)) {
            abort(404);
        }

        $segments = explode('/', $path);
        $locale = config('app.locale', 'en');
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

    private function injectHeadingIds(string $html): string
    {
        $maxLevel = config('blogr-docs.toc.max_level', 3);

        return preg_replace_callback(
            '/<h(['.implode('', range(2, $maxLevel)).'])(.*?)>(.*?)<\/h\1>/i',
            function ($matches) {
                $level = $matches[1];
                $attributes = $matches[2];
                $text = strip_tags($matches[3]);

                if (preg_match('/id=["\']/i', $attributes)) {
                    return $matches[0];
                }

                $id = Str::slug($text);
                $heading = '<h'.$level.$attributes.' id="'.$id.'">';
                $heading .= '<a href="#'.$id.'" class="heading-permalink" aria-hidden="true">#</a> ';
                $heading .= $text;
                $heading .= '</h'.$level.'>';

                return $heading;
            },
            $html
        );
    }
}
