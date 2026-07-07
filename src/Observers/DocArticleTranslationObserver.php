<?php

namespace Happytodev\BlogrDocs\Observers;

use Happytodev\BlogrDocs\Models\DocArticleTranslation;
use Illuminate\Support\Str;

class DocArticleTranslationObserver
{
    public function saving(DocArticleTranslation $translation): void
    {
        if (! $translation->isDirty('content')) {
            return;
        }

        $content = $translation->content;

        if ($content === null) {
            $translation->headings = null;
            return;
        }

        preg_match_all('/^(#{2,3})\s+(.+)$/m', $content, $matches, PREG_SET_ORDER);

        $headings = [];

        foreach ($matches as $match) {
            $level = strlen(trim($match[1]));
            $text = trim($match[2]);
            $anchor = Str::slug($text);

            $headings[] = [
                'level' => $level,
                'text' => $text,
                'anchor' => $anchor,
            ];
        }

        $translation->headings = $headings;
    }
}
