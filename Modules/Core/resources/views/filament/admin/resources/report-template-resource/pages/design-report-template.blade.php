@php
    use Modules\Core\Services\ReportTemplateService;
    $systemBlocks = app(ReportTemplateService::class)->getSystemBlocks();
@endphp
<x-filament-panels::page>
    <div class="w-full">
        <!-- Header Bar -->
        <div
            class="flex items-center justify-between w-full bg-gray-100 px-4 py-1 rounded-md mb-4 border-b border-gray-200"
            style="padding: 24px;">
            <span class="font-medium text-lg text-gray-700">Report Builder</span>
            <x-filament::button type="button" color="primary"
                                style="box-shadow: 0 1px 2px #0002; float: right !important;">
                Save Template
            </x-filament::button>
        </div>
        <!-- Main Content: 2 Columns -->
        <div
            x-data="{
            bands: [
                { name: 'Header Band', color: '#e5e9f0', border: '#81a1c1', blocks: [] },
                { name: 'Detail Group Header Band', color: '#eceff4', border: '#8fbcbb', blocks: [] },
                { name: 'Details Band', color: '#d8dee9', border: '#5e81ac', blocks: [] },
                { name: 'Detail Group Footer Band', color: '#e5e9f0', border: '#81a1c1', blocks: [] },
                { name: 'Footer Band', color: '#eceff4', border: '#8fbcbb', blocks: [] },
            ],
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
                    block = this.blocks[blockIdx];
                    this.blocks.splice(blockIdx, 1);
                    this.blocks = [...this.blocks];
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
                const block = this.bands[sourceBandIdx].blocks[blockIdx];
                this.blocks.push(block);
                this.blocks = [...this.blocks];
                const updatedSourceBand = { ...this.bands[sourceBandIdx], blocks: this.bands[sourceBandIdx].blocks.filter(b => b.id !== blockId) };
                this.bands.splice(sourceBandIdx, 1, updatedSourceBand);
                this.bands = [...this.bands];
            },
        }"
            class="flex w-full max-w-full min-h-screen gap-6 border-4 border-primary-500 relative z-50 box-border"
            style="display: flex !important; width: 100%; max-width: 100%; min-height: 100vh; gap: 1.5rem; border: 4px solid #3b82f6; position: relative; z-index: 50; box-sizing: border-box;">
            <div
                style="flex: 1 1 0; padding: 1.5rem; border: 1px solid #000; background: #ebcb8b; color: #2e3440; border-radius: 1rem; min-width: 0; box-sizing: border-box;">
                <div style="font-size: 1.25rem; font-weight: bold; margin-bottom: 1rem;">Design (Alpine & Nord)
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
                            addBlockToBand(idx, blockId, sourceBandIdx !== '' ? Number(sourceBandIdx) : null);
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
                    <div class="font-semibold text-base mb-2 text-gray-700">
                        {{ __('Available Blocks (Alpine & Nord)') }}
                    </div>
                    <ul class="space-y-2">
                        <template x-for="block in blocks" :key="block.id">
                            <li>
                                <div
                                    class="cursor-grab flex items-center px-2 py-1 rounded"
                                    style="background: #bf616a; color: #eceff4; border-radius: 0.5rem; padding: 0.5rem 1rem; display: flex; align-items: center; font-weight: normal; margin-right: 0.5rem; margin-bottom: 0.5rem; border: 1px solid #bf616a;"
                                    draggable="true"
                                    x-on:dragstart="event.dataTransfer.setData('blockId', block.id); event.dataTransfer.setData('sourceBandIdx', '');"
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
