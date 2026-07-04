@extends('blogr-docs::layouts.docs')

@section('doc-content')
    <header class="mb-12">
        <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">
            {{ $seoTitle }}
        </h1>
        @if($seoDescription)
            <p class="text-xl text-gray-600 dark:text-gray-300">
                {{ $seoDescription }}
            </p>
        @endif
    </header>

    @if(isset($learningPaths) && $learningPaths->isNotEmpty())
        <section class="mb-12">
            <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-6">
                {{ __('blogr-docs::ui.learning_paths') }}
            </h2>
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($learningPaths as $path)
                    <a href="{{ $path->url() }}"
                       class="block p-6 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-primary-500 dark:hover:border-primary-500 transition-colors">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                            {{ $path->title }}
                        </h3>
                        @if($path->description)
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                {{ $path->description }}
                            </p>
                        @endif
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    <section>
        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-6">
            {{ __('blogr-docs::ui.sections') }}
        </h2>
        <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($tree as $node)
                @php $item = $node['translation']; @endphp
                @if($item)
                    <a href="{{ $item->url() }}"
                       class="block p-6 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-primary-500 dark:hover:border-primary-500 transition-colors">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                            {{ $item->title }}
                        </h3>
                        @if($item->excerpt)
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                {{ $item->excerpt }}
                            </p>
                        @endif
                    </a>
                @endif
            @endforeach
        </div>
    </section>
@endsection
