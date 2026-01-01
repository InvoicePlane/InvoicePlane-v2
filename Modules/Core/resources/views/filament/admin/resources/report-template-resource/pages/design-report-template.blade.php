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

            startDragging(event, blockId) {
                this.isDragging = true;
                this.draggedBlockId = blockId;
                this.selectedBlockId = blockId;
                this.startX = event.clientX;
                this.startY = event.clientY;
                this.originalX = this.blocks[blockId].position.x;
                this.originalY = this.blocks[blockId].position.y;

                window.addEventListener('mousemove', this.handleDragging);
                window.addEventListener('mouseup', this.stopDragging);
            },

            handleDragging(event) {
                if (!this.isDragging || !this.draggedBlockId) return;

                const deltaX = event.clientX - this.startX;
                const deltaY = event.clientY - this.startY;

                // Grid size is assumed to be roughly based on container width / 12
                const container = document.getElementById('report-canvas');
                const gridWidth = container.offsetWidth / 12;
                const gridHeight = 40; // Fixed height for grid rows in designer

                const newX = Math.round(this.originalX + (deltaX / gridWidth));
                const newY = Math.round(this.originalY + (deltaY / gridHeight));

                if (newX === this.blocks[this.draggedBlockId].position.x && newY === this.blocks[this.draggedBlockId].position.y) {
                    return;
                }

                // Basic validation before dispatching to backend
                if (newX >= 0 && newX <= 12 - this.blocks[this.draggedBlockId].position.width && newY >= 0) {
                     this.$wire.updateBlockPosition(this.draggedBlockId, {
                            x: newX,
                            y: newY,
                            width: this.blocks[this.draggedBlockId].position.width,
                            height: this.blocks[this.draggedBlockId].position.height
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
        <div class="flex items-center justify-between">
            <div class="flex gap-2">
                <x-filament::button
                    wire:click="save"
                    color="primary"
                    icon="heroicon-m-check"
                >
                    Save Template
                </x-filament::button>

                <x-filament::button
                    color="gray"
                    tag="a"
                    :href="static::getResource()::getUrl('index')"
                >
                    Cancel
                </x-filament::button>
            </div>
        </div>

        <div class="grid grid-cols-12 gap-6">
            <!-- Sidebar: Available Blocks -->
            <div class="col-span-3 space-y-4">
                <x-filament::section>
                    <x-slot name="heading">Available Blocks</x-slot>
                    <div class="flex flex-col gap-2">
                        @php
                            $systemBlocks = app(\Modules\Core\Services\ReportTemplateService::class)->getSystemBlocks();
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
                        <x-slot name="heading">Block Settings</x-slot>
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

            <!-- Canvas Area -->
            <div class="col-span-9">
                <div
                    class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden shadow-sm">
                    <!-- Band Header -->
                    <div
                        class="bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-800 px-4 py-2 flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-gray-500">Header</span>
                    </div>

                    <div
                        id="report-canvas"
                        class="relative min-h-[1200px] w-full p-4 bg-[radial-gradient(#e5e7eb_1px,transparent_1px)] dark:bg-[radial-gradient(#374151_1px,transparent_1px)] [background-size:20px_20px]"
                        style="display: grid; grid-template-columns: repeat(12, 1fr); grid-auto-rows: 40px; gap: 0;"
                    >
                        <!-- Band Overlays -->
                        <div
                            class="absolute inset-x-0 top-0 h-[200px] border-b-2 border-dashed border-gray-200 dark:border-gray-800 pointer-events-none flex items-start justify-center p-2">
                            <span
                                class="text-[10px] font-bold uppercase tracking-widest text-gray-300 dark:text-gray-700">Header Band</span>
                        </div>
                        <div
                            class="absolute inset-x-0 top-[200px] h-[120px] border-b-2 border-dashed border-gray-200 dark:border-gray-800 pointer-events-none flex items-start justify-center bg-gray-50/30 dark:bg-gray-800/10 p-2">
                            <span
                                class="text-[10px] font-bold uppercase tracking-widest text-gray-300 dark:text-gray-700">Group Header Band</span>
                        </div>
                        <div
                            class="absolute inset-x-0 top-[320px] h-[400px] border-b-2 border-dashed border-gray-200 dark:border-gray-800 pointer-events-none flex items-start justify-center p-2">
                            <span
                                class="text-[10px] font-bold uppercase tracking-widest text-gray-300 dark:text-gray-700">Details Band</span>
                        </div>
                        <div
                            class="absolute inset-x-0 top-[720px] h-[120px] border-b-2 border-dashed border-gray-200 dark:border-gray-800 pointer-events-none flex items-start justify-center bg-gray-50/30 dark:bg-gray-800/10 p-2">
                            <span
                                class="text-[10px] font-bold uppercase tracking-widest text-gray-300 dark:text-gray-700">Group Footer Band</span>
                        </div>
                        <div
                            class="absolute inset-x-0 top-[840px] h-[360px] pointer-events-none flex items-start justify-center p-2">
                            <span
                                class="text-[10px] font-bold uppercase tracking-widest text-gray-300 dark:text-gray-700">Footer Band</span>
                        </div>

                        @foreach($blocks as $blockId => $block)
                            <div
                                wire:key="{{ $blockId }}"
                                @mousedown="startDragging($event, '{{ $blockId }}')"
                                class="absolute border-2 transition-all duration-75 cursor-move flex flex-col p-2 bg-white dark:bg-gray-800 shadow-sm rounded overflow-hidden {{ $selectedBlockId === $blockId ? 'border-primary-500 z-20' : 'border-gray-200 dark:border-gray-700 z-10' }}"
                                style="
                                    left: calc({{ $block['position']['x'] }} * 100% / 12);
                                    top: calc({{ $block['position']['y'] }} * 40px);
                                    width: calc({{ $block['position']['width'] }} * 100% / 12);
                                    height: calc({{ $block['position']['height'] }} * 40px);
                                "
                            >
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
                        @endforeach
                    </div>

                    <!-- Band Footer -->
                    <div
                        class="bg-gray-50 dark:bg-gray-800/50 border-t border-gray-200 dark:border-gray-800 px-4 py-2 flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-gray-500">Footer</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
