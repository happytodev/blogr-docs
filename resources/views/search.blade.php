@extends('blogr-docs::layouts.docs')

@section('seo-data')
    @php
        $seoData = [
            'title' => $seoTitle ?? __('blogr-docs::ui.search_results_default'),
            'description' => $seoDescription ?? '',
        ];
    @endphp
@endsection

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <main class="max-w-3xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-6">
            {{ __('blogr-docs::ui.search_results', ['query' => $query]) }}
        </h1>

        @if($results->isEmpty())
            <p class="text-gray-500 dark:text-gray-400">
                {{ __('blogr-docs::ui.no_results') }}
            </p>
        @else
            <div class="space-y-6">
                @foreach($results as $result)
                    <article>
                        <a href="{{ $result['url'] }}"
                           class="block p-4 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-primary-500 dark:hover:border-primary-500 transition-colors">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">
                                {{ $result['title'] }}
                            </h2>
                            @if($result['excerpt'])
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ Str::limit(strip_tags($result['excerpt']), 200) }}
                                </p>
                            @endif
                        </a>
                    </article>
                @endforeach
            </div>
        @endif

        <div class="mt-8">
            <a href="{{ url(config('blogr-docs.prefix', 'docs')) }}"
               class="text-sm text-primary-600 dark:text-primary-400 hover:underline">
                &larr; {{ __('blogr-docs::ui.back_to_docs') }}
            </a>
        </div>
    </main>
</div>
@endsection
