<x-filament-panels::page>
    <div class="fi-section rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        <div class="flex flex-wrap items-end gap-4">
            <label class="grid gap-1 text-sm">
                <span class="font-medium text-gray-700 dark:text-gray-300">{{ trans('ip.date_from') }}</span>
                <x-filament::input.wrapper>
                    <x-filament::input type="date" wire:model.live="dateFrom" />
                </x-filament::input.wrapper>
            </label>

            <label class="grid gap-1 text-sm">
                <span class="font-medium text-gray-700 dark:text-gray-300">{{ trans('ip.date_to') }}</span>
                <x-filament::input.wrapper>
                    <x-filament::input type="date" wire:model.live="dateTo" />
                </x-filament::input.wrapper>
            </label>

            <label class="grid gap-1 text-sm">
                <span class="font-medium text-gray-700 dark:text-gray-300">{{ trans('ip.client') }}</span>
                <x-filament::input.wrapper>
                    <x-filament::input.select wire:model.live="clientId">
                        <option value="">{{ trans('ip.all_clients') }}</option>
                        @foreach ($this->getClientOptions() as $client)
                            <option value="{{ $client['id'] }}">{{ $client['name'] }}</option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            </label>
        </div>
    </div>

    {{ $this->table }}

    <div class="rounded-xl bg-gray-50 p-4 text-sm font-medium text-gray-700 ring-1 ring-gray-950/5 dark:bg-gray-800 dark:text-gray-200 dark:ring-white/10">
        {{ $this->summaryLine() }}
    </div>
</x-filament-panels::page>
