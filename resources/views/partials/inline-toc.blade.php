@if(isset($descendantTree) && $descendantTree->isNotEmpty())
    <section class="mt-12 pt-8 border-t border-gray-200 dark:border-gray-700">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
            {{ __('blogr-docs::ui.in_this_section') }}
        </h2>
        <ul class="space-y-3">
            @foreach($descendantTree as $node)
                @include('blogr-docs::partials.inline-toc-node', ['node' => $node, 'level' => 0])
            @endforeach
        </ul>
    </section>
@endif
