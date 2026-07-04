@props(['tree' => [], 'activeId' => null, 'level' => 0])

<ul class="space-y-1 {{ $level > 0 ? 'ml-4 border-l border-gray-200 dark:border-gray-700 pl-3' : '' }}">
    @foreach($tree as $node)
        @php
            $translation = $node['translation'];
            $article = $node['article'];
            $isActive = $article->id === $activeId;
            $hasChildren = $node['has_children'];
            $icon = $article->icon;
        @endphp

        @if($translation)
            <li>
                <a href="{{ $translation->url() }}"
                   class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition-colors
                          {{ $isActive
                              ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-300 font-medium'
                              : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                    @if($icon && config('blogr-docs.sidebar.show_icons', true))
                        <x-dynamic-component :component="'heroicon-o-'.$icon" class="w-4 h-4 flex-shrink-0" />
                    @endif
                    <span class="flex-1 truncate">{{ $translation->title }}</span>
                    @if($hasChildren)
                        <svg class="w-4 h-4 flex-shrink-0 transition-transform {{ $isActive ? 'rotate-90' : '' }}"
                             xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                        </svg>
                    @endif
                </a>

                @if($hasChildren && ($isActive || $level < 1))
                    @include('blogr-docs::partials.sidebar', [
                        'tree' => $node['children'],
                        'activeId' => $activeId,
                        'level' => $level + 1,
                    ])
                @endif
            </li>
        @endif
    @endforeach
</ul>
