<x-filament-panels::page>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($this->getTemplates() as $template)
            <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <h3 class="text-base font-semibold text-gray-950 dark:text-white">
                            {{ $template['manifest']['name'] ?? $template['slug'] }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            {{ trans('ip.' . $template['type']) }}
                            · {{ $template['scope'] === 'system' ? trans('ip.system_template') : trans('ip.company_template') }}
                        </p>
                    </div>
                    <x-filament::badge :color="$template['scope'] === 'system' ? 'info' : 'success'">
                        {{ $template['slug'] }}
                    </x-filament::badge>
                </div>

                <div class="mt-4 flex flex-wrap gap-2">
                    @if ($this->builderUrl($template))
                        <x-filament::button
                            tag="a"
                            size="sm"
                            :href="$this->builderUrl($template)"
                            icon="heroicon-o-paint-brush"
                        >
                            {{ trans('ip.open_builder') }}
                        </x-filament::button>
                    @endif

                    <x-filament::button
                        size="sm"
                        color="gray"
                        icon="heroicon-o-document-duplicate"
                        wire:click="mountAction('clone', {{ \Illuminate\Support\Js::from(['scope' => $template['scope'], 'type' => $template['type'], 'slug' => $template['slug']]) }})"
                    >
                        {{ trans('ip.clone') }}
                    </x-filament::button>

                    @if ($this->canModify($template))
                        <x-filament::button
                            size="sm"
                            color="gray"
                            icon="heroicon-o-pencil-square"
                            wire:click="mountAction('rename', {{ \Illuminate\Support\Js::from(['scope' => $template['scope'], 'type' => $template['type'], 'slug' => $template['slug'], 'name' => $template['manifest']['name'] ?? '']) }})"
                        >
                            {{ trans('ip.rename') }}
                        </x-filament::button>

                        <x-filament::button
                            size="sm"
                            color="danger"
                            icon="heroicon-o-trash"
                            wire:click="mountAction('delete', {{ \Illuminate\Support\Js::from(['scope' => $template['scope'], 'type' => $template['type'], 'slug' => $template['slug']]) }})"
                        >
                            {{ trans('ip.delete') }}
                        </x-filament::button>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    @if (empty($this->getTemplates()))
        <div class="rounded-xl bg-white p-6 text-sm text-gray-500 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:text-gray-400 dark:ring-white/10">
            {{ trans('ip.no_report_templates') }}
        </div>
    @endif

    <x-filament-actions::modals />
</x-filament-panels::page>
