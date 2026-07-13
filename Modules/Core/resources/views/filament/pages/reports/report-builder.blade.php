<x-filament-panels::page>
    @unless ($this->canSave())
        <div class="rounded-xl bg-warning-50 p-4 text-sm text-warning-700 ring-1 ring-warning-600/20 dark:bg-warning-500/10 dark:text-warning-400">
            {{ trans('ip.system_template_read_only') }}
        </div>
    @endunless

    <form wire:submit="save">
        {{ $this->form }}
    </form>

    <x-filament-actions::modals />
</x-filament-panels::page>
