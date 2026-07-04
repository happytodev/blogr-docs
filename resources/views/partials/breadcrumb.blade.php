@if(isset($breadcrumbs) && count($breadcrumbs) > 0)
    <nav aria-label="Breadcrumb" class="mb-6">
        <ol class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
            <li>
                <a href="{{ url(config('blogr-docs.prefix', 'docs')) }}"
                   class="hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                    {{ __('blogr-docs::ui.docs') }}
                </a>
            </li>
            @foreach($breadcrumbs as $crumb)
                <li class="flex items-center gap-2">
                    <svg class="w-4 h-4 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                    </svg>
                    @if($loop->last)
                        <span class="text-gray-900 dark:text-white font-medium" aria-current="page">
                            {{ $crumb['title'] }}
                        </span>
                    @else
                        <a href="{{ $crumb['url'] }}"
                           class="hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                            {{ $crumb['title'] }}
                        </a>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif
