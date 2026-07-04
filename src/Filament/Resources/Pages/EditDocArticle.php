<?php

namespace Happytodev\BlogrDocs\Filament\Resources\Pages;

use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Happytodev\Blogr\Services\LocaleService;
use Happytodev\Blogr\Services\Translation\CodeBlockPreserver;
use Happytodev\Blogr\Services\Translation\TranslationProviderFactory;
use Happytodev\Blogr\Services\TranslationUsageService;
use Happytodev\BlogrDocs\Filament\Resources\DocArticleResource;
use Happytodev\BlogrDocs\Models\DocArticleTranslation;
use Illuminate\Support\Str;

class EditDocArticle extends EditRecord
{
    protected static string $resource = DocArticleResource::class;

    protected function getHeaderActions(): array
    {
        $provider = app(TranslationProviderFactory::class)->make();
        $actions = [];

        if ($provider) {
            $existingLocales = $this->record->translations()
                ->pluck('locale')
                ->toArray();

            $allLocales = app(LocaleService::class)->getAvailable();

            $sourceOptions = collect($allLocales)
                ->filter(fn ($l) => in_array($l, $existingLocales))
                ->mapWithKeys(fn ($l) => [$l => app(LocaleService::class)->localeLabel($l)])
                ->toArray();

            $targetOptions = collect($allLocales)
                ->mapWithKeys(fn ($l) => [$l => app(LocaleService::class)->localeLabel($l)])
                ->toArray();

            $actions[] = Actions\Action::make('translateWithAI')
                ->label('Translate with AI')
                ->icon('heroicon-o-language')
                ->color('success')
                ->form([
                    Select::make('source_locale')
                        ->label('Source language')
                        ->options($sourceOptions)
                        ->default($this->record->default_locale)
                        ->required(),
                    Select::make('target_locale')
                        ->label('Target language')
                        ->options($targetOptions)
                        ->required()
                        ->rule('different:source_locale'),
                ])
                ->action(function (array $data) use ($provider) {
                    $this->translateWithAI($provider, $data['source_locale'], $data['target_locale']);
                });
        }

        return $actions;
    }

    protected function translateWithAI($provider, string $sourceLocale, string $targetLocale): void
    {
        $sourceTranslation = $this->record->translations()
            ->where('locale', $sourceLocale)
            ->first();

        if (! $sourceTranslation) {
            Notification::make()
                ->title("No source translation found for {$sourceLocale}")
                ->danger()
                ->send();

            return;
        }

        try {
            $fields = [
                'title', 'excerpt', 'content', 'seo_title', 'seo_description', 'seo_keywords',
            ];

            $translated = [];
            $charCount = 0;
            $preserver = new CodeBlockPreserver;

            foreach ($fields as $field) {
                $sourceValue = $sourceTranslation->{$field} ?? '';
                if (! empty(trim($sourceValue))) {
                    $translatedValue = $field === 'content'
                        ? $preserver->translateContent($provider, $sourceValue, $sourceLocale, $targetLocale)
                        : $provider->translate($sourceValue, $sourceLocale, $targetLocale);
                    $translated[$field] = $translatedValue;
                    $charCount += mb_strlen($sourceValue) + mb_strlen($translatedValue);
                }
            }

            $translated['slug'] = Str::slug($provider->translate(
                Str::headline($sourceTranslation->slug), $sourceLocale, $targetLocale
            ));

            $targetTranslation = $this->record->translations()
                ->where('locale', $targetLocale)
                ->first();

            $translated['slug'] = $this->ensureUniqueTranslationSlug($translated['slug'], $targetTranslation?->id);

            if ($targetTranslation) {
                $targetTranslation->update($translated);
            } else {
                $translated['locale'] = $targetLocale;
                $translated['doc_article_id'] = $this->record->id;
                DocArticleTranslation::create($translated);
            }

            $this->record->load('translations');
            $this->refreshFormData(['translations']);

            app(TranslationUsageService::class)->trackUsage(
                config('blogr.translation.provider', 'none'),
                $charCount
            );

            Notification::make()
                ->title("Translation {$targetLocale} completed with AI")
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Translation error')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function ensureUniqueTranslationSlug(string $slug, ?int $excludeId = null): string
    {
        $candidate = $slug;
        $counter = 1;

        while (DocArticleTranslation::where('slug', $candidate)
            ->when($excludeId, fn ($query) => $query->where('id', '!=', $excludeId))
            ->exists()
        ) {
            $candidate = $slug.'-'.$counter++;
        }

        return $candidate;
    }
}
