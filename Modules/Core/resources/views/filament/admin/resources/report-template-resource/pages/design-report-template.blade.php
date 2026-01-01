<div
    x-data="{
        blocks: [
            { id: 'example-block-1', label: 'Example Block' },
            // Add more blocks here if needed
        ],
        bands: [
            { name: 'Header Band', color: '#e5e9f0', border: '#81a1c1', blocks: [] },
            { name: 'Detail Group Header Band', color: '#eceff4', border: '#8fbcbb', blocks: [] },
            { name: 'Details Band', color: '#d8dee9', border: '#5e81ac', blocks: [] },
            { name: 'Detail Group Footer Band', color: '#e5e9f0', border: '#81a1c1', blocks: [] },
            { name: 'Footer Band', color: '#eceff4', border: '#8fbcbb', blocks: [] },
        ],
        hoveredBand: null,
        dragBlockId: null,
        addBlockToBand(bandIdx, blockId) {
            if (!blockId) return;
            // Find and remove block from available blocks
            const blockIdx = this.blocks.findIndex(b => b.id === blockId);
            if (blockIdx === -1) return;
            const block = this.blocks[blockIdx];
            // Add block to band (replace band object for Alpine reactivity)
            if (this.bands[bandIdx]) {
                // Create a new band object and a new blocks array
                const updatedBand = { ...this.bands[bandIdx], blocks: [...this.bands[bandIdx].blocks, block] };
                // Replace the band in the bands array
                this.bands.splice(bandIdx, 1, updatedBand);
                // Replace the bands array reference for Alpine reactivity
                this.bands = [...this.bands];
                // Debug: log bands after drop
                console.log('Bands after drop:', JSON.stringify(this.bands));
                // Force Alpine to update UI
                this.$nextTick(() => {});
            } else {
                return;
            }
            this.blocks.splice(blockIdx, 1);
            this.blocks = [...this.blocks];
        },
    }"
    style="display: flex !important; width: 100%; max-width: 100%; min-height: 100vh; gap: 1.5rem; border: 4px solid #3b82f6; position: relative; z-index: 50; box-sizing: border-box;">
    <div
        style="flex: 1 1 0; padding: 1.5rem; border: 1px solid #000; background: #ebcb8b; color: #2e3440; border-radius: 1rem; min-width: 0; box-sizing: border-box;">
        <div style="font-size: 1.25rem; font-weight: bold; margin-bottom: 1rem;">Design (Alpine & Nord)</div>
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
                        addBlockToBand(idx, blockId);
                    "
                >
                    <span x-text="band.name + ' (Drop here)'" style="margin-right: 1rem;"></span>
                    <div x-show="band.blocks.length === 0"
                         style="color: #888; font-size: 0.95rem; font-weight: normal; margin-right: 0.5rem; margin-bottom: 0.5rem;">
                        No blocks in this band
                    </div>
                    <template x-for="block in band.blocks" :key="block.id">
                        <div
                            style="background: #bf616a; color: #eceff4; border-radius: 0.5rem; padding: 0.5rem 1rem; display: inline-block; font-weight: normal; margin-right: 0.5rem; margin-bottom: 0.5rem;">
                            <span x-text="block.label"></span>
                        </div>
                    </template>
                </div>
            </template>
        </div>
    </div>
    <div
        style="flex: 1 1 0; max-width: 20rem; padding: 1.5rem; border: 1px solid #000; background: #bf616a; color: #eceff4; border-radius: 1rem; min-width: 0; position: sticky; top: 1.5rem; box-sizing: border-box;">
        <div style="font-size: 1.25rem; font-weight: bold; margin-bottom: 1rem;">Available Blocks (Alpine & Nord)</div>
        <ul style="list-style: none; padding: 0; margin: 0;">
            <template x-for="block in blocks" :key="block.id">
                <li
                    :id="block.id"
                    draggable="true"
                    x-on:dragstart="event.dataTransfer.setData('blockId', block.id)"
                    style="background: #fff; border: 1px solid #e5e7eb; border-radius: 0.5rem; padding: 0.75rem 1rem; box-shadow: 0 1px 2px 0 #0001; color: #000; margin-bottom: 0.5rem; cursor: grab; font-weight: bold;"
                >
                    <span x-text="block.label"></span>
                </li>
            </template>
        </ul>
    </div>
</div>
