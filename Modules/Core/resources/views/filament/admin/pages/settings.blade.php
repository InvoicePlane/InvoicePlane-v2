<x-filament-panels::page>
    <x-filament-schemas::form wire:submit="submit">
        {{ $this->form }}

        <x-filament-schemas::actions
            :actions="$this->getCachedFormActions()"
            :full-width="$this->hasFullWidthFormActions()"
        />
    </x-filament-schemas::form>
</x-filament-panels::page>
