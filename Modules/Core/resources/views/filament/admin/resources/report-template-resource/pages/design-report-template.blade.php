@php
    use Modules\Core\Services\ReportTemplateService;
    $systemBlocks = app(ReportTemplateService::class)->getSystemBlocks();
@endphp
<x-filament-panels::page>
    <div class="w-full"
         x-data="{
            bands: [
                { name: 'Header Band', key: 'header', color: '#e5e9f0', border: '#81a1c1', blocks: [] },
                { name: 'Detail Group Header Band', key: 'group_header', color: '#eceff4', border: '#8fbcbb', blocks: [] },
                { name: 'Details Band', key: 'details', color: '#d8dee9', border: '#5e81ac', blocks: [] },
                { name: 'Detail Group Footer Band', key: 'group_footer', color: '#e5e9f0', border: '#81a1c1', blocks: [] },
                { name: 'Footer Band', key: 'footer', color: '#eceff4', border: '#8fbcbb', blocks: [] },
            ],
            init() {
                const loadedBlocks = @js($blocks);
                Object.values(loadedBlocks).forEach(block => {
                    const band = this.bands.find(b => b.key === block.band);
                    if (band) {
                        band.blocks.push(block);
                    } else {
                        // Default to header if band not found
                        this.bands[0].blocks.push(block);
                    }
                });
            },
            blocks: [
                @foreach($systemBlocks as $type => $blockDto)
                    { id: '{{ $type }}', label: '{{ $blockDto->getLabel() }}' },
                @endforeach
            ],
            hoveredBand: null,
            dragBlockId: null,
            dragSourceBandIdx: null,
            addBlockToBand(bandIdx, blockId, sourceBandIdx = null) {
                if (!blockId) return;
                let block = null;
                if (sourceBandIdx === null) {
                    // From available blocks
                    const blockIdx = this.blocks.findIndex(b => b.id === blockId);
                    if (blockIdx === -1) return;
                    // Create a deep copy and give it a unique ID if it doesn't have one that looks like a real block ID
                    // Real block IDs start with 'block_'
                    const sourceBlock = this.blocks[blockIdx];
                    block = {
                        ...sourceBlock,
                        id: 'block_' + sourceBlock.id + '_' + Math.random().toString(36).substr(2, 9),
                        type: sourceBlock.id
                    };
                    // We don't splice from available blocks, allow multiple uses
                } else {
                    // From another band
                    const blockIdx = this.bands[sourceBandIdx].blocks.findIndex(b => b.id === blockId);
                    if (blockIdx === -1) return;
                    block = this.bands[sourceBandIdx].blocks[blockIdx];
                    const updatedSourceBand = { ...this.bands[sourceBandIdx], blocks: this.bands[sourceBandIdx].blocks.filter(b => b.id !== blockId) };
                    this.bands.splice(sourceBandIdx, 1, updatedSourceBand);
                    this.bands = [...this.bands];
                }
                // Add block to target band
                if (this.bands[bandIdx]) {
                    const updatedBand = { ...this.bands[bandIdx], blocks: [...this.bands[bandIdx].blocks, block] };
                    this.bands.splice(bandIdx, 1, updatedBand);
                    this.bands = [...this.bands];
                    this.$nextTick(() => {});
                }
            },
            addBlockToAvailable(blockId, sourceBandIdx) {
                if (!blockId || sourceBandIdx === null) return;
                const blockIdx = this.bands[sourceBandIdx].blocks.findIndex(b => b.id === blockId);
                if (blockIdx === -1) return;
                // When removing from a band, just delete it (it's an instance)
                const updatedSourceBand = { ...this.bands[sourceBandIdx], blocks: this.bands[sourceBandIdx].blocks.filter(b => b.id !== blockId) };
                this.bands.splice(sourceBandIdx, 1, updatedSourceBand);
                this.bands = [...this.bands];
            },
            save() {
                const bandsToSave = this.bands.map(band => ({
                    ...band,
                    blocks: band.blocks.map(block => {
                        // Ensure block has the correct band key before saving
                        return { ...block, band: band.key };
                    })
                }));
                this.$wire.save(bandsToSave);
                console.log('Bands to save:', JSON.stringify(bandsToSave, null, 2));
            },
        }"
    >
        {{-- Header Bar --}}
        <div class="flex items-center justify-between w-full mb-4 fi-header"
             style="padding: 44px; background: #527397 !important;">
            <span class="ml-2 font-medium text-white">Report Builder</span>
            <div class="flex gap-2 ml-auto">
                <x-filament::button
                    x-on:click.prevent="save()"
                    color="primary"
                    icon="heroicon-m-check"
                    class="font-bold"
                    style="box-shadow: 0 1px 2px #0002; float: right !important;"
                >
                    Save Report
                </x-filament::button>
                <x-filament::button
                    color="gray"
                    tag="a"
                    :href="static::getResource()::getUrl('index')"
                    class="font-bold"
                    style="box-shadow: 0 1px 2px #0002; float: right !important;"
                >
                    Close Builder
                </x-filament::button>
            </div>
        </div>
        {{-- Main Content: 2 Columns --}}
        <div
            class="flex w-full max-w-full min-h-screen gap-6 border-4 border-primary-500 relative z-50 box-border"
            style="display: flex !important; width: 100%; max-width: 100%; min-height: 100vh; gap: 1.5rem; border: 4px solid #3b82f6; position: relative; z-index: 50; box-sizing: border-box;">
            <div
                style="flex: 1 1 0; padding: 1.5rem; border: 1px solid #000; background: #ebcb8b; color: #2e3440; border-radius: 1rem; min-width: 0; box-sizing: border-box;">
                <div style="font-size: 1.25rem; font-weight: bold; margin-bottom: 1rem;">Design Area
                </div>
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <template x-for="(band, idx) in bands" :key="band.name">
                        <div
                            class="droppable-band"
                            :style="{
                            minHeight: idx === 2 ? '120px' : '80px',
                            background: (hoveredBand === idx) ? (idx === 2 ? '#e5e9f0' : '#eceff4') : band.color,
                            borderRadius: '0.5rem',
                            border: '2px dashed ' + band.border,
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'flex-start',
                            fontWeight: 'bold',
                            color: '#2e3440',
                            transition: 'background 0.2s',
                            marginBottom: '0.5rem',
                            flexWrap: 'wrap',
                            padding: '0.5rem 1rem',
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
                            <span x-text="band.name + ' (Drop here)'" style="margin-right: 1rem;"></span>
                            <template x-if="band.blocks.length === 0">
                                <div
                                    style="color: #888; font-size: 0.95rem; font-weight: normal; margin-right: 0.5rem; margin-bottom: 0.5rem;">
                                    No blocks in this band
                                </div>
                            </template>
                            <template x-for="(block, blockIdx) in band.blocks" :key="block.id">
                                <div
                                    :draggable="true"
                                    x-on:dragstart="event.dataTransfer.setData('blockId', block.id); event.dataTransfer.setData('sourceBandIdx', idx);"
                                    style="background: #bf616a; color: #eceff4; border-radius: 0.5rem; padding: 0.5rem 1rem; display: inline-block; font-weight: normal; margin-right: 0.5rem; margin-bottom: 0.5rem; cursor: grab;"
                                >
                                    <span x-text="block.label"></span>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
            <div class="col-span-12 md:col-span-3">
                <div class="rounded-lg shadow bg-white p-4 border border-blue-200">
                    <div class="font-semibold text-base mb-2 text-gray-700"
                         style="font-size: 1.25rem; font-weight: bold; margin-bottom: 1rem;">
                        @lang('ip.available_blocks')
                    </div>
                    <ul class="space-y-2">
                        <template x-for="block in blocks" :key="block.id">
                            <li>
                                <div
                                    class="cursor-grab flex items-center px-2 py-1 rounded"
                                    style="background: #145390 !important; color: #eceff4 !important; font-weight: 400 !important; border-radius: 0.5rem !important; padding: 0.5rem 1rem !important; display: flex !important; align-items: center !important; margin-right: 0.5rem !important; margin-bottom: 0.5rem !important; border: 1px solid #bf616a !important;"
                                    draggable="true"
                                    x-on:dragstart="event.dataTransfer.setData('blockId', block.id); event.dataTransfer.setData('sourceBandIdx', 'available');"
                                >
                                    <x-filament::icon name="heroicon-m-plus" class="w-4 h-4 mr-2 text-white"/>
                                    <span class="text-white text-sm" x-text="block.label"></span>
                                </div>
                            </li>
                        </template>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
