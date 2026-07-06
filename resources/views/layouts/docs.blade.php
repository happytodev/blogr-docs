@extends('blogr::layouts.blog')

@push('styles')
<style>[x-cloak] { display: none !important; }</style>
@endpush

@section('seo-data')
    @php
        $seoData = [
            'title' => $seoTitle ?? config('blogr-docs.seo.default_title', 'Documentation'),
            'description' => $seoDescription ?? config('blogr-docs.seo.default_description'),
            'keywords' => $seoKeywords ?? '',
        ];
    @endphp
@endsection

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8"
     x-data="{ readingMode: localStorage.getItem('docs-reading-mode') === 'true' }"
     x-init="$watch('readingMode', val => localStorage.setItem('docs-reading-mode', val))">
    <div class="flex gap-8">
        @if(isset($tree) && $tree->isNotEmpty())
            <aside class="w-56 flex-shrink-0 hidden lg:block" x-show="!readingMode">
                <nav class="sticky top-24 overflow-y-auto max-h-[calc(100vh-8rem)]">
                    <div class="mb-4">
                        @if(config('blogr-docs.search.enabled', true))
                            <form action="{{ url(config('blogr-docs.prefix', 'docs')) }}" method="GET">
                                <input type="search" name="q" placeholder="{{ __('blogr-docs::ui.search') }}"
                                       class="w-full px-3 py-1.5 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            </form>
                        @endif
                    </div>
                    @include('blogr-docs::partials.sidebar', ['tree' => $tree, 'activeId' => $article->id ?? null])
                </nav>
            </aside>
        @endif

        <main class="flex-1 min-w-0">
            @include('blogr-docs::partials.breadcrumb')

            <div class="flex gap-8">
                <article class="prose prose-lg dark:prose-invert max-w-none docs-content flex-1 min-w-0">
                    @yield('doc-content')
                </article>

                @yield('toc')
            </div>

            @include('blogr-docs::partials.prev-next')
        </main>
    </div>
</div>
@endsection
