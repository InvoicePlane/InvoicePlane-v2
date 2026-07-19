@php
    use Filament\Support\Enums\MaxWidth;
@endphp

<x-filament-panels::page.simple
    :heading="__('Company Settings')"
    :subheading="null"
    :maxWidth="MaxWidth::Large"
>
    <x-filament-forms::form wire:submit="save">
        {{ $this->form }}

        <x-filament-forms::actions
            :actions="$this->getCachedFormActions()"
            :full-width="$this->hasFullWidthFormActions()"
        />
    </x-filament-forms::form>
</x-filament-panels::page.simple>
