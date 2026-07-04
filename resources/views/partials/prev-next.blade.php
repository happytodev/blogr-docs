@if(isset($prevTranslation) || isset($nextTranslation))
    <nav class="flex items-center justify-between mt-12 pt-8 border-t border-gray-200 dark:border-gray-700">
        <div>
            @if(isset($prevTranslation))
                <a href="{{ $prevTranslation->url() }}"
                   class="group flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                    <svg class="w-5 h-5 transition-transform group-hover:-translate-x-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
                    </svg>
                    <div class="text-left">
                        <span class="block text-xs uppercase tracking-wider">{{ __('blogr-docs::ui.previous') }}</span>
                        <span class="block text-sm font-medium text-gray-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400">
                            {{ $prevTranslation->title }}
                        </span>
                    </div>
                </a>
            @endif
        </div>

        <div class="text-right">
            @if(isset($nextTranslation))
                <a href="{{ $nextTranslation->url() }}"
                   class="group flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                    <div class="text-right">
                        <span class="block text-xs uppercase tracking-wider">{{ __('blogr-docs::ui.next') }}</span>
                        <span class="block text-sm font-medium text-gray-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400">
                            {{ $nextTranslation->title }}
                        </span>
                    </div>
                    <svg class="w-5 h-5 transition-transform group-hover:translate-x-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                    </svg>
                </a>
            @endif
        </div>
    </nav>
@endif
