<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        <div class="flex justify-end mt-6">
            <button type="submit"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500">
                {{ __('blogr-docs::ui.save_settings') }}
            </button>
        </div>
    </form>
</x-filament-panels::page>
