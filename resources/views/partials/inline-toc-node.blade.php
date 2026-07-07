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
            @php
                $groupedHeadings = [];
                $currentH2Index = null;
                foreach ($headings as $h) {
                    if ($h['level'] === 2) {
                        $h['children'] = [];
                        $groupedHeadings[] = $h;
                        $currentH2Index = count($groupedHeadings) - 1;
                    } elseif ($h['level'] === 3 && $currentH2Index !== null) {
                        $groupedHeadings[$currentH2Index]['children'][] = $h;
                    }
                }
            @endphp
            <ul class="mt-1 ml-4 space-y-1 border-l-2 border-gray-200 dark:border-gray-700 pl-3">
                @foreach($groupedHeadings as $h2)
                    <li class="text-sm text-gray-600 dark:text-gray-400">
                        <a href="{{ $translation->url() }}#{{ $h2['anchor'] }}"
                           class="hover:text-gray-900 dark:hover:text-gray-200"
                           title="{{ $h2['text'] }}">
                            {{ $h2['text'] }}
                        </a>
                        @if(!empty($h2['children']))
                            <ul class="mt-1 ml-3 space-y-1 border-l-2 border-gray-200 dark:border-gray-700 pl-3">
                                @foreach($h2['children'] as $h3)
                                    <li class="text-sm text-gray-600 dark:text-gray-400">
                                        <a href="{{ $translation->url() }}#{{ $h3['anchor'] }}"
                                           class="hover:text-gray-900 dark:hover:text-gray-200"
                                           title="{{ $h3['text'] }}">
                                            {{ $h3['text'] }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
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
