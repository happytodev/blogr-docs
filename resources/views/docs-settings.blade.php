<x-filament::page>
    {{ $this->form }}

    <x-filament::footer.actions :actions="$this->getFormActions()" />
</x-filament::page>
