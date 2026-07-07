@props(['node', 'level' => 0])

<li>
    @php
        $translation = $node['translation'] ?? null;
        $headings = $node['headings'] ?? [];
        $children = $node['children'] ?? collect();
    @endphp

    @if($translation)
        <a href="{{ $translation->url() }}"
           class="font-medium text-primary-600 dark:text-primary-400 hover:underline">
            {{ $translation->title }}
        </a>

        @if(!empty($headings))
            <ul class="mt-1 ml-4 space-y-1 border-l-2 border-gray-200 dark:border-gray-700 pl-3">
                @foreach($headings as $heading)
                    <li class="text-sm text-gray-600 dark:text-gray-400">
                        <a href="{{ $translation->url() }}#{{ $heading['anchor'] }}"
                           class="hover:text-gray-900 dark:hover:text-gray-200"
                           title="{{ $heading['text'] }}">
                            <span class="inline-flex items-center gap-1">
                                <span class="text-xs text-gray-400 dark:text-gray-500 font-mono">
                                    {{ 'H' . $heading['level'] }}
                                </span>
                                {{ $heading['text'] }}
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif

        @if($children->isNotEmpty())
            <ul class="mt-2 space-y-2">
                @foreach($children as $child)
                    @include('blogr-docs::partials.inline-toc-node', ['node' => $child, 'level' => $level + 1])
                @endforeach
            </ul>
        @endif
    @endif
</li>
