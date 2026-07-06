@extends('blogr-docs::layouts.docs')

@section('doc-content')
    @if($isFallback ?? false)
        <div class="mb-6 p-4 bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-400 dark:border-yellow-600 rounded-r-lg">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="w-5 h-5 text-yellow-400 dark:text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div class="ml-3 flex-1">
                    <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                        {{ __('blogr-docs::ui.translation_unavailable_title', ['locale' => strtoupper($requestedLocale ?? '')]) }}
                    </h3>
                    <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                        <p>{{ __('blogr-docs::ui.translation_unavailable_message', ['requested' => strtoupper($requestedLocale ?? ''), 'showing' => strtoupper($locale)]) }}</p>
                    </div>
                    @if($availableTranslations->isNotEmpty())
                        <div class="mt-3">
                            <p class="text-xs text-yellow-700 dark:text-yellow-400 mb-2">
                                {{ __('blogr-docs::ui.translation_available_in') }}:
                            </p>
                            <div class="flex flex-wrap gap-2">
                                @foreach($availableTranslations as $t)
                                    <a href="{{ $t['url'] }}"
                                       class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-200 dark:bg-yellow-800 text-yellow-800 dark:text-yellow-200 hover:bg-yellow-300 dark:hover:bg-yellow-700 transition-colors">
                                        {{ strtoupper($t['locale']) }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <header class="mb-8">
        <div class="flex items-start justify-between gap-4">
            <div class="flex-1">
                <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    {{ $title }}
                </h1>
                @if($translation->excerpt)
                    <p class="text-xl text-gray-600 dark:text-gray-300">
                        {{ $translation->excerpt }}
                    </p>
                @endif
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
                <button @click="readingMode = !readingMode; localStorage.setItem('docs-reading-mode', readingMode)"
                        class="p-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                        :title="readingMode ? '{{ __('blogr-docs::ui.exit_reading_mode') }}' : '{{ __('blogr-docs::ui.enter_reading_mode') }}'"
                        x-cloak>
                    <svg x-show="!readingMode" class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    <svg x-show="readingMode" class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </button>
                @if(config('blogr-docs.pdf.enabled', false) && isset($pdfUrl))
                    <a href="{{ $pdfUrl }}"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M10 2a.75.75 0 01.75.75v7.5l1.97-1.97a.75.75 0 111.06 1.06l-3.25 3.25a.75.75 0 01-1.06 0L6.47 9.34a.75.75 0 111.06-1.06l1.97 1.97V2.75A.75.75 0 0110 2z"/>
                            <path d="M3.75 13.5a.75.75 0 01.75.75v2.25h11V14.25a.75.75 0 011.5 0v2.25a1.5 1.5 0 01-1.5 1.5H4.5a1.5 1.5 0 01-1.5-1.5V14.25a.75.75 0 01.75-.75z"/>
                        </svg>
                        {{ __('blogr-docs::ui.pdf_export') }}
                    </a>
                @endif
            </div>
        </div>
    </header>

    @if($htmlContent)
        <div class="markdown-content">
            {!! $htmlContent !!}
        </div>
    @elseif($translation->content)
        <div class="markdown-content">
            {!! $translation->content !!}
        </div>
    @endif
@endsection

@section('toc')
    @if($tocHtml ?? false)
        <aside class="w-40 flex-shrink-0 hidden lg:block" x-show="!readingMode">
            <nav class="sticky top-24 overflow-y-auto max-h-[calc(100vh-8rem)] border-l border-gray-200 dark:border-gray-700 pl-3">
                <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">
                    {{ __('blogr-docs::ui.on_this_page') }}
                </h3>
                <div class="space-y-1 text-xs">
                    {!! $tocHtml !!}
                </div>
            </nav>
        </aside>
    @endif
@endsection
