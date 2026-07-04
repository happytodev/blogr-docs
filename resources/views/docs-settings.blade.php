@php
    use Filament\Support\Facades\FilamentView;
@endphp

<x-filament::page>
    <x-filament::card>
        {{ $this->form }}
    </x-filament::card>

    <x-filament::card>
        <div class="flex justify-end">
            {{ $this->getAction('save') }}
        </div>
    </x-filament::card>
</x-filament::page>
