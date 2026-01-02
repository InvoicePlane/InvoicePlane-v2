@php
    use Modules\Core\Services\ReportTemplateService;
    use Modules\Core\Transformers\BlockTransformer;
    use Modules\Core\Enums\ReportBand;
    
    $systemBlocks = app(ReportTemplateService::class)->getSystemBlocks();
    $systemBlocksArray = array_map(fn($block) => BlockTransformer::toArray($block), $systemBlocks);
    
    // Build bands array with enum-based colors
    $bandsConfig = [];
    foreach (ReportBand::cases() as $bandEnum) {
        $bandsConfig[] = [
            'name' => $bandEnum->getLabel() . ' Band',
            'key' => $bandEnum->value,
            'colorClass' => $bandEnum->getColorClass(),
            'borderClass' => $bandEnum->getBorderColorClass(),
            'order' => $bandEnum->getOrder(),
        ];
    }
@endphp
<x-filament-panels::page>
    <div class="w-full max-w-full">
        <div class="w-full"
             x-data="{
            bands: @js($bandsConfig).map(band => ({ ...band, blocks: [] })),
            systemBlockDefinitions: @js($systemBlocksArray),
            init() {
                window.reportBuilder = this;
                try {
                    const loadedBlocks = @js($blocks);
                    console.log('Initializing with loaded blocks:', loadedBlocks);

                    // If loadedBlocks is already grouped by band (associative array/object)
                    if (loadedBlocks && typeof loadedBlocks === 'object' && !Array.isArray(loadedBlocks) &&
                        Object.keys(loadedBlocks).some(key => ['header', 'group_header', 'details', 'group_footer', 'footer'].includes(key))) {
                        this.bands.forEach(band => {
                            if (loadedBlocks[band.key]) {
                                band.blocks = loadedBlocks[band.key];
                            }
                        });
                    } else {
                        // Fallback for flat array (backward compatibility)
                        Object.values(loadedBlocks).forEach(block => {
                            const band = this.bands.find(b => b.key === block.band);
                            if (band) {
                                band.blocks.push(block);
                            } else {
                                // Default to header if band not found
                                this.bands[0].blocks.push(block);
                            }
                        });
                    }
                    console.log('Initialization complete. Bands:', JSON.parse(JSON.stringify(this.bands)));
                } catch (e) {
                    console.error('Error during initialization:', e);
                }
            },
            availableBlocks: @js(array_values(array_map(fn($type, $blockDto) => ['id' => $type, 'label' => $blockDto->getLabel()], array_keys($systemBlocks), $systemBlocks))),
            hoveredBand: null,
            dragBlockId: null,
            dragSourceBandIdx: null,
            addBlockToBand(bandIdx, blockId, sourceBandIdx = null) {
                if (!blockId) return;
                let block = null;
                if (sourceBandIdx === null) {
                    // From available blocks
                    const blockIdx = this.availableBlocks.findIndex(b => b.id === blockId);
                    if (blockIdx === -1) return;
                    // Create a deep copy and give it a unique ID if it doesn't have one that looks like a real block ID
                    // Real block IDs start with 'block_'
                    const sourceBlock = this.availableBlocks[blockIdx];

                    // Find block width from systemBlocks
                    const systemBlocks = this.systemBlockDefinitions;
                    const systemBlock = systemBlocks[sourceBlock.id] || null;
                    const position = systemBlock ? systemBlock.position : {x: 0, y: 0, width: 6, height: 4};

                    block = {
                        ...sourceBlock,
                        id: 'block_' + sourceBlock.id + '_' + Math.random().toString(36).substr(2, 9),
                        type: sourceBlock.id,
                        position: position,
                        config: systemBlock ? systemBlock.config : {fields: []}
                    };
                    // We don't splice from available blocks, allow multiple uses
                } else {
                    // From another band
                    const blockIdx = this.bands[sourceBandIdx].blocks.findIndex(b => b.id === blockId);
                    if (blockIdx === -1) return;
                    block = this.bands[sourceBandIdx].blocks[blockIdx];
                    const updatedSourceBand = {
                        ...this.bands[sourceBandIdx],
                        blocks: this.bands[sourceBandIdx].blocks.filter(b => b.id !== blockId)
                    };
                    this.bands.splice(sourceBandIdx, 1, updatedSourceBand);
                    this.bands = [...this.bands];
                }
                // Add block to target band
                if (this.bands[bandIdx]) {
                    const updatedBand = {
                        ...this.bands[bandIdx],
                        blocks: [...this.bands[bandIdx].blocks, block]
                    };
                    this.bands.splice(bandIdx, 1, updatedBand);
                    this.bands = [...this.bands];
                    this.$nextTick(() => {
                    });
                }
            },
            addBlockToAvailable(blockId, sourceBandIdx) {
                if (!blockId || sourceBandIdx === null) return;
                const blockIdx = this.bands[sourceBandIdx].blocks.findIndex(b => b.id === blockId);
                if (blockIdx === -1) return;
                // When removing from a band, just delete it (it's an instance)
                const updatedSourceBand = {
                    ...this.bands[sourceBandIdx],
                    blocks: this.bands[sourceBandIdx].blocks.filter(b => b.id !== blockId)
                };
                this.bands.splice(sourceBandIdx, 1, updatedSourceBand);
                this.bands = [...this.bands];
            },
            save() {
                const bandsToSave = this.bands.map(band => ({
                    ...band,
                    blocks: band.blocks.map(block => {
                        // Ensure block has the correct band key before saving
                        return {...block, band: band.key};
                    })
                }));
                this.$wire.save(bandsToSave);
                console.log('Bands to save:', JSON.stringify(bandsToSave, null, 2));
            },
        }"
        >
            {{-- Header Bar --}}
            <div class="flex items-center justify-between w-full mb-6 pb-4 border-b border-gray-200 dark:border-gray-700 fi-header p-3 bg-primary-600 dark:bg-primary-700 rounded-xl mb-8">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-white">Report Designer</h2>
                    <p class="text-sm text-gray-100 dark:text-gray-200">Design your report layout by dragging and dropping blocks into bands.</p>
                </div>
                <div class="flex items-center gap-3">
                    <x-filament::button
                        color="gray"
                        tag="a"
                        :href="static::getResource()::getUrl('index')"
                        icon="heroicon-m-x-mark"
                    >
                        Close
                    </x-filament::button>
                    <x-filament::button
                        x-on:click.prevent="save()"
                        color="warning"
                        icon="heroicon-m-check"
                    >
                        Save Changes
                    </x-filament::button>
                </div>
            </div>

            {{-- Help Card (Pro Tip) --}}
            <div class="mb-6 bg-warning-100 dark:bg-warning-900/20 rounded-2xl border-b-4 border-warning-600 dark:border-warning-700 shadow-lg p-4">
                <div class="flex items-center gap-4">
                    <x-filament::icon name="heroicon-m-light-bulb" class="w-8 h-8 text-warning-600 dark:text-warning-500"/>
                    <div>
                        <p class="text-xs font-black text-gray-900 dark:text-gray-100 uppercase tracking-widest">Pro Tip</p>
                        <p class="text-sm text-gray-700 dark:text-gray-300 font-medium leading-relaxed">Drag blocks into any band to build
                            your layout. Use the <strong>Edit</strong> button on any block to configure its fields and
                            appearance globally!</p>
                    </div>
                </div>
            </div>

            {{-- Main Content: Grid layout for side-by-side design --}}
            <div class="w-full grid grid-cols-12 gap-8 items-start">

                {{-- Design Area (Left) - 9 columns --}}
                <div class="col-span-9 space-y-8 rounded-2xl border-2 border-dashed border-gray-400 dark:border-gray-600 bg-gray-800 dark:bg-gray-950 p-10">
                    <template x-for="(band, idx) in bands" :key="band.key">
                        <div
                            class="relative bg-white dark:bg-gray-900 border-2 rounded-xl shadow-sm transition-all mb-8"
                            :class="[
                                hoveredBand === idx ? 'ring-2 ring-primary-500 border-transparent' : 'border-gray-300 dark:border-gray-700',
                                band.colorClass
                            ]"
                        >
                            {{-- Band Header (Floating-style Label) --}}
                            <div
                                class="absolute left-6 -top-3 text-xs font-black tracking-widest text-white rounded-lg shadow-sm z-10 px-3 py-2"
                                :class="band.borderClass"
                            >
                                <span x-text="band.name"></span>
                            </div>

                            <div
                                class="min-h-[140px] rounded-lg border-2 border-dashed transition-colors grid grid-cols-2 gap-6 items-start content-start p-6"
                                :class="{
                                    'border-primary-400 bg-primary-500/5': hoveredBand === idx,
                                    'border-gray-300/30 dark:border-gray-700/30': hoveredBand !== idx,
                                }"
                                x-on:dragover.prevent="hoveredBand = idx"
                                x-on:dragleave="hoveredBand = null"
                                x-on:drop.prevent="
                                hoveredBand = null;
                                const blockId = event.dataTransfer.getData('blockId');
                                const sourceBandIdx = event.dataTransfer.getData('sourceBandIdx');
                                if (sourceBandIdx === 'available') {
                                    addBlockToBand(idx, blockId, null);
                                } else if (sourceBandIdx !== '') {
                                    addBlockToBand(idx, blockId, Number(sourceBandIdx));
                                }
                            "
                            >
                                <template x-if="band.blocks.length === 0">
                                    <div class="col-span-2 flex flex-col items-center justify-center py-6 text-gray-500 dark:text-gray-400 italic pointer-events-none opacity-60">
                                        <x-filament::icon name="heroicon-m-arrow-down-tray" class="w-8 h-8 mb-2"/>
                                        <span class="text-sm font-bold">Drop blocks here</span>
                                    </div>
                                </template>

                                <template x-for="(block, blockIdx) in band.blocks" :key="block.id">
                                    <div
                                        :draggable="true"
                                        x-on:dragstart="event.dataTransfer.setData('blockId', block.id); event.dataTransfer.setData('sourceBandIdx', idx);"
                                        class="group relative flex flex-col items-start bg-danger-500 dark:bg-danger-600 rounded-2xl cursor-grab active:cursor-grabbing hover:-translate-y-1 transition-all shadow-xl min-h-[100px] p-10 border-4 border-dashed border-white/40"
                                        :class="'col-span-' + (block.position && block.position.width >= 8 ? '2' : '1')"
                                    >
                                        <div class="flex items-center justify-between w-full mb-1">
                                            <div class="flex items-center gap-2">
                                                <x-filament::icon name="heroicon-m-bars-2" class="w-6 h-6 text-white/90"/>
                                                <button
                                                    type="button"
                                                    @click.stop="console.log('Clicked block for config:', block.id, block.slug); $wire.mountAction('configureBlock', { blockSlug: block.slug })"
                                                    class="bg-white/20 hover:bg-white/40 rounded-lg text-white transition-colors shadow-inner px-2 py-1 flex items-center gap-1 relative z-20"
                                                    title="Configure Fields"
                                                >
                                                    <x-filament::icon name="heroicon-m-cog-6-tooth" class="w-4 h-4"/>
                                                    <span class="text-[10px] font-bold uppercase tracking-tighter">Edit</span>
                                                </button>
                                            </div>
                                            <button
                                                type="button"
                                                x-on:click.stop="addBlockToAvailable(block.id, idx)"
                                                class="bg-black/20 hover:bg-black/40 rounded-lg text-white transition-colors shadow-inner p-1"
                                                title="Remove Block"
                                            >
                                                <x-filament::icon name="heroicon-m-x-mark" class="w-5 h-5"/>
                                            </button>
                                        </div>
                                        <div class="w-full cursor-pointer" @click.stop="console.log('Clicked block:', block.id); $wire.mountAction('configureBlock', { blockSlug: block.slug })">
                                            <span class="text-sm font-black text-white uppercase tracking-wider overflow-hidden text-ellipsis w-full whitespace-nowrap self-start" x-html="block.label.replace(/ /g, '&nbsp;')"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Sidebar: Available Blocks (Right) - 3 columns --}}
                <div class="col-span-3 sticky top-4 p-3">
                    <div class="bg-white dark:bg-gray-900 border-b-4 border-gray-200 dark:border-gray-700 rounded-2xl shadow-xl overflow-hidden p-1">
                        <div class="border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 p-4">
                            <h3 class="font-black text-gray-900 dark:text-white flex items-center gap-3 uppercase tracking-wider text-sm">
                                <x-filament::icon name="heroicon-m-squares-plus" class="w-6 h-6 text-primary-600 dark:text-primary-500"/>
                                @lang('ip.available_blocks')
                            </h3>
                        </div>

                        <div class="bg-white dark:bg-gray-900 p-4">
                            <div class="grid grid-cols-1 gap-4">
                                <template x-for="block in availableBlocks" :key="block.id">
                                    <div
                                        class="group flex flex-col items-start gap-2 bg-primary-500 dark:bg-primary-600 border-b-4 border-primary-700 dark:border-primary-800 rounded-xl cursor-grab active:cursor-grabbing hover:brightness-110 transition-all shadow-lg min-h-[80px] p-3 w-full"
                                        draggable="true"
                                        x-on:dragstart="event.dataTransfer.setData('blockId', block.id); event.dataTransfer.setData('sourceBandIdx', 'available');"
                                    >
                                        <div class="flex-shrink-0 w-8 h-8 flex items-center justify-center bg-white/20 rounded-lg text-white group-hover:bg-white/30 transition-colors">
                                            <x-filament::icon name="heroicon-m-plus" class="w-5 h-5"/>
                                        </div>
                                        <span class="text-sm font-black text-white uppercase tracking-tight overflow-hidden text-ellipsis w-full whitespace-nowrap self-start" x-html="block.label.replace(/ /g, '&nbsp;')"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <x-filament-actions::modals/>
</x-filament-panels::page>
