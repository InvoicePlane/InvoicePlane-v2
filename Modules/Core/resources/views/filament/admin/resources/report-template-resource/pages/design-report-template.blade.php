@php use Modules\Core\Services\ReportTemplateService; @endphp
<x-filament-panels::page>
    <div
        x-data="{
            blocks: @entangle('blocks'),
            selectedBlockId: @entangle('selectedBlockId'),
            isDragging: false,
            draggedBlockId: null,
            startX: 0,
            startY: 0,
            originalX: 0,
            originalY: 0,
            originalBand: null,

            startDragging(event, blockId) {
                if (event.target.closest('button, input, select, textarea')) return;
                this.isDragging = true;
                this.draggedBlockId = blockId;
                this.selectedBlockId = blockId;
                this.startX = event.clientX;
                this.startY = event.clientY;
                this.originalX = this.blocks[blockId].position.x;
                this.originalY = this.blocks[blockId].position.y;
                this.originalBand = this.blocks[blockId].band;

                window.addEventListener('mousemove', this.handleDragging);
                window.addEventListener('mouseup', this.stopDragging);
            },

            handleDragging(event) {
                if (!this.isDragging || !this.draggedBlockId) return;

                const currentBand = this.blocks[this.draggedBlockId].band || 'header';
                const container = document.getElementById('band-' + currentBand);
                const rect = container.getBoundingClientRect();
                const gridWidth = rect.width / 12;
                const gridHeight = 40;

                const newX = Math.round(this.originalX + (event.clientX - this.startX) / gridWidth);
                const newY = Math.round(this.originalY + (event.clientY - this.startY) / gridHeight);

                if (newX === this.blocks[this.draggedBlockId].position.x && newY === this.blocks[this.draggedBlockId].position.y) {
                    return;
                }

                if (newX >= 0 && newX <= 12 - this.blocks[this.draggedBlockId].position.width && newY >= 0) {
                     this.$wire.updateBlockPosition(this.draggedBlockId, {
                            x: newX,
                            y: newY,
                            width: this.blocks[this.draggedBlockId].position.width,
                            height: this.blocks[this.draggedBlockId].position.height,
                            band: currentBand
                        });
                }
            },

            stopDragging() {
                this.isDragging = false;
                this.draggedBlockId = null;
                window.removeEventListener('mousemove', this.handleDragging);
                window.removeEventListener('mouseup', this.stopDragging);
            },

            addBlock(type) {
                this.$wire.addBlock(type);
            }
        }"
        class="flex flex-col gap-6"
    >
        <div
            class="flex items-center justify-between bg-white dark:bg-gray-900 px-6 py-4 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm">
            <div class="flex items-center gap-4">
                <h1 class="text-xl font-bold">@lang('ip.design_report_template')</h1>
            </div>
            <div class="flex gap-3">
                <x-filament::button
                    color="gray"
                    tag="a"
                    icon="heroicon-m-x-mark"
                    :href="static::getResource()::getUrl('index')"
                >
                    @lang('ip.cancel')
                </x-filament::button>

                <x-filament::button
                    wire:click="save"
                    color="primary"
                    icon="heroicon-m-check"
                >
                    @lang('ip.save_template')
                </x-filament::button>
            </div>
        </div>

        <div class="grid grid-cols-12 gap-6 items-start">
            {{-- Canvas Area --}}
            <div class="col-span-12 lg:col-span-9 space-y-8">
                @php
                    $bands = [
                        'header' => 'Header',
                        'group_header' => 'Group Detail Header',
                        'details' => 'Details',
                        'group_footer' => 'Group Detail Footer',
                        'footer' => 'Footer',
                    ];
                @endphp

                @foreach($bands as $bandKey => $bandLabel)
                    <div
                        class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden shadow-sm"
                    >
                        <div
                            class="bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-800 px-4 py-2 flex items-center justify-between">
                            <span
                                class="text-xs font-bold uppercase tracking-wider text-gray-500">{{ $bandLabel }}</span>
                        </div>

                        <div
                            id="band-{{ $bandKey }}"
                            class="relative min-h-[120px] w-full p-1 bg-[radial-gradient(#e5e7eb_1px,transparent_1px)] dark:bg-[radial-gradient(#374151_1px,transparent_1px)] [background-size:20px_20px]"
                            style="display: grid; grid-template-columns: repeat(12, 1fr); grid-auto-rows: 40px; gap: 0;"
                        >
                            @foreach($blocks as $blockId => $block)
                                @if(($block['band'] ?? 'header') === $bandKey)
                                    <div
                                        wire:key="{{ $blockId }}"
                                        @mousedown="startDragging($event, '{{ $blockId }}')"
                                        class="absolute border-2 transition-all duration-75 cursor-move flex flex-col bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm shadow-sm rounded overflow-hidden {{ $selectedBlockId === $blockId ? 'border-primary-500 z-30 ring-2 ring-primary-500/20' : 'border-gray-200 dark:border-gray-700 z-10 hover:border-gray-300 dark:hover:border-gray-600' }}"
                                        style="
                                                left: calc({{ $block['position']['x'] }} * 100% / 12);
                                                top: calc({{ $block['position']['y'] }} * 40px);
                                                width: calc({{ $block['position']['width'] }} * 100% / 12);
                                                height: calc({{ $block['position']['height'] }} * 40px);
                                                touch-action: none;
                                            "
                                    >
                                        <div class="flex-1 flex flex-col p-[4px]">
                                            <div class="flex items-center justify-between mb-1">
                                                <span
                                                    class="text-[10px] font-bold text-gray-400 uppercase truncate">{{ $block['type'] }}</span>
                                                <div class="flex gap-1">
                                                    @if($block['isCloned'])
                                                        <x-filament::icon
                                                            icon="heroicon-m-square-2-stack"
                                                            class="w-3 h-3 text-gray-400"
                                                        />
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="flex-1 flex items-center justify-center text-center">
                                                <span class="text-sm font-medium">{{ $block['label'] }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Sidebar: Available Blocks --}}
            <div class="col-span-12 lg:col-span-3 space-y-4 lg:sticky lg:top-6">
                <x-filament::section>
                    <x-slot name="heading">@lang('ip.available_blocks')</x-slot>
                    <div class="grid grid-cols-1 gap-2">
                        @php
                            $systemBlocks = app(ReportTemplateService::class)->getSystemBlocks();
                        @endphp
                        @foreach($systemBlocks as $type => $blockDto)
                            <x-filament::button
                                wire:click="addBlock('{{ $type }}')"
                                color="gray"
                                size="sm"
                                outlined
                                class="justify-start"
                                icon="heroicon-m-plus"
                            >
                                {{ $blockDto->getLabel() }}
                            </x-filament::button>
                        @endforeach
                    </div>
                </x-filament::section>

                @if($selectedBlockId && isset($blocks[$selectedBlockId]))
                    <x-filament::section>
                        <x-slot name="heading">@lang('ip.block_settings')</x-slot>
                        <div class="space-y-4">
                            <div>
                                <label class="text-sm font-medium">Label</label>
                                <x-filament::input
                                    type="text"
                                    wire:model="blocks.{{ $selectedBlockId }}.label"
                                />
                            </div>

                            @if(isset($blocks[$selectedBlockId]['config']) && count($blocks[$selectedBlockId]['config']) > 0)
                                <div class="space-y-2">
                                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Block
                                        Configuration</label>
                                    <div
                                        class="space-y-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                        @foreach($blocks[$selectedBlockId]['config'] as $key => $value)
                                            <div class="flex flex-col gap-1">
                                                <label
                                                    class="text-[10px] font-bold uppercase tracking-wider text-gray-500">{{ str_replace('_', ' ', $key) }}</label>
                                                @if(is_bool($value))
                                                    <x-filament::input.checkbox
                                                        wire:model="blocks.{{ $selectedBlockId }}.config.{{ $key }}"
                                                    />
                                                @elseif(is_numeric($value))
                                                    <x-filament::input
                                                        type="number"
                                                        wire:model="blocks.{{ $selectedBlockId }}.config.{{ $key }}"
                                                        size="sm"
                                                    />
                                                @else
                                                    <x-filament::input
                                                        type="text"
                                                        wire:model="blocks.{{ $selectedBlockId }}.config.{{ $key }}"
                                                        size="sm"
                                                    />
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <div class="flex gap-2">
                                <x-filament::button
                                    wire:click="cloneBlock('{{ $selectedBlockId }}')"
                                    color="gray"
                                    size="sm"
                                    icon="heroicon-m-squares-plus"
                                >
                                    Clone
                                </x-filament::button>

                                <x-filament::button
                                    wire:click="deleteBlock('{{ $selectedBlockId }}')"
                                    color="danger"
                                    size="sm"
                                    icon="heroicon-m-trash"
                                >
                                    Delete
                                </x-filament::button>
                            </div>
                        </div>
                    </x-filament::section>
                @endif
            </div>
        </div>
    </div>
</x-filament-panels::page>
