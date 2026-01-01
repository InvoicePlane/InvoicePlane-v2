@php
    use Modules\Core\Services\ReportTemplateService;
    $systemBlocks = app(ReportTemplateService::class)->getSystemBlocks();
@endphp
<x-filament-panels::page>
    <div class="w-full" style="max-width: 100% !important;">
        <div class="w-full"
             x-data="{
            bands: [
                { name: 'Header Band', key: 'header', color: '#e5e9f0', darkColor: '#2e3440', border: '#81a1c1', blocks: [] },
                { name: 'Detail Group Header Band', key: 'group_header', color: '#eceff4', darkColor: '#3b4252', border: '#8fbcbb', blocks: [] },
                { name: 'Details Band', key: 'details', color: '#d8dee9', darkColor: '#434c5e', border: '#5e81ac', blocks: [] },
                { name: 'Detail Group Footer Band', key: 'group_footer', color: '#e5e9f0', darkColor: '#2e3440', border: '#81a1c1', blocks: [] },
                { name: 'Footer Band', key: 'footer', color: '#eceff4', darkColor: '#3b4252', border: '#8fbcbb', blocks: [] },
            ],
            isModalOpen: false,
            editingBlock: null,
            availableFields: @js($this->getAvailableFields()),
            openBlockModal(block) {
                this.editingBlock = JSON.parse(JSON.stringify(block));
                // Ensure editingBlock has a fields array in config
                if (!this.editingBlock.config) this.editingBlock.config = {};
                if (!this.editingBlock.config.fields) this.editingBlock.config.fields = [];

                // Load the latest system configuration for this block type to ensure we have the latest fields
                const systemBlocks = @js($systemBlocks);
                if (systemBlocks[this.editingBlock.type] && systemBlocks[this.editingBlock.type].config) {
                    // The issue says: " Once i save the modal, that block needs to be saved to json file for that
             block.
        "
        // This implies we are editing the GLOBAL block definition.
        this.editingBlock.config = JSON.parse(JSON.stringify(systemBlocks[this.editingBlock.type].config));
        if (!this.editingBlock.config.fields) this.editingBlock.config.fields = [];
        }

        this.isModalOpen = true;
        },
        closeBlockModal() {
        this.isModalOpen = false;
        this.editingBlock = null;
        },
        saveBlockModal() {
        // Update the block in the bands
        this.bands.forEach(band => {
        const idx = band.blocks.findIndex(b => b.id === this.editingBlock.id);
        if (idx !== -1) {
        band.blocks[idx].config = this.editingBlock.config;
        }
        });

        // Save the block definition to the system JSON via Livewire
        this.$wire.saveBlockConfiguration(this.editingBlock.type, this.editingBlock.config);

        new FilamentNotification()
        .title('Block configuration saved')
        .success()
        .send();

        this.closeBlockModal();
        },
        addFieldToBlock(fieldId) {
        if (!this.editingBlock.config.fields.find(f => f.id === fieldId)) {
        const field = this.availableFields.find(f => f.id === fieldId);
        this.editingBlock.config.fields.push(field);
        }
        },
        removeFieldFromBlock(fieldId) {
        this.editingBlock.config.fields = this.editingBlock.config.fields.filter(f => f.id !== fieldId);
        },
        moveField(index, direction) {
        const fields = this.editingBlock.config.fields;
        const newIndex = index + direction;
        if (newIndex >= 0 && newIndex < fields.length) {
        const temp = fields[index];
        fields[index] = fields[newIndex];
        fields[newIndex] = temp;
        }
        },
        init() {
        const loadedBlocks = @js($blocks);

        // If loadedBlocks is already grouped by band (associative array/object)
        if (loadedBlocks && typeof loadedBlocks === 'object' && !Array.isArray(loadedBlocks) &&
        Object.keys(loadedBlocks).some(key => ['header', 'group_header', 'details', 'group_footer',
        'footer'].includes(key))) {
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

        // Find block width from systemBlocks
        const systemBlocks = @js($systemBlocks);
        const systemBlock = systemBlocks[sourceBlock.id] || null;
        const position = systemBlock ? systemBlock.position : { x: 0, y: 0, width: 6, height: 4 };

        block = {
        ...sourceBlock,
        id: 'block_' + sourceBlock.id + '_' + Math.random().toString(36).substr(2, 9),
        type: sourceBlock.id,
        position: position,
        config: systemBlock ? systemBlock.config : { fields: [] }
        };
        // We don't splice from available blocks, allow multiple uses
        } else {
        // From another band
        const blockIdx = this.bands[sourceBandIdx].blocks.findIndex(b => b.id === blockId);
        if (blockIdx === -1) return;
        block = this.bands[sourceBandIdx].blocks[blockIdx];
        const updatedSourceBand = { ...this.bands[sourceBandIdx], blocks: this.bands[sourceBandIdx].blocks.filter(b =>
        b.id !== blockId) };
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
        const updatedSourceBand = { ...this.bands[sourceBandIdx], blocks: this.bands[sourceBandIdx].blocks.filter(b =>
        b.id !== blockId) };
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
        <div
            class="flex items-center justify-between w-full mb-6 pb-4 border-b border-gray-200 dark:border-white/10 fi-header"
            style="padding: 12px; background: #527397 !important; border-radius: 12px; margin-bottom: 2rem;">
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-white">Report Designer</h2>
                <p class="text-sm text-gray-200">Design your report layout by dragging and dropping
                    blocks into bands.</p>
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
                    color="primary"
                    icon="heroicon-m-check"
                    style="background-color: #ebcb8b !important; color: #2e3440 !important;"
                >
                    Save Changes
                </x-filament::button>
            </div>
        </div>

        {{-- Help Card (Pro Tip) moved under header --}}
        <div class="mb-6 bg-[#ebcb8b] rounded-2xl border-b-4 border-[#d08770] shadow-lg p-4">
            <div class="flex items-center gap-4">
                <x-filament::icon name="heroicon-m-light-bulb" class="w-8 h-8 text-[#bf616a]"/>
                <div>
                    <p class="text-xs font-black text-[#2e3440] uppercase tracking-widest">Pro Tip</p>
                    <p class="text-sm text-[#4c566a] font-medium leading-relaxed">Drag blocks into any band to build
                        your layout. Click on a block to configure its fields and appearance!</p>
                </div>
            </div>
        </div>

        {{-- Main Content: Robust CSS Grid for forced side-by-side layout --}}
        <div class="w-full gap-8 items-start"
             style="display: grid !important; grid-template-columns: 75% 25% !important; align-items: flex-start !important; max-width: none !important; width: 100% !important;">

            {{-- Design Area (Left) - 75% width --}}
            <div
                class="space-y-8 rounded-2xl border-2 border-dashed border-[#4c566a] dark:border-[#81a1c1]"
                style="background-color: #2e3440 !important; min-width: 0 !important; max-width: none !important; padding: 40px !important;"
                :style="document.documentElement.classList.contains('dark') ? 'background-color: #1b2027 !important' : 'background-color: #2e3440 !important'"
            >
                <template x-for="(band, idx) in bands" :key="band.key">
                    <div
                        class="relative bg-white dark:bg-gray-900 border border-gray-300 dark:border-white/10 rounded-xl shadow-sm transition-all mb-8"
                        :class="hoveredBand === idx ? 'ring-2 ring-primary-500 border-transparent' : ''"
                        :style="document.documentElement.classList.contains('dark') ? { backgroundColor: band.darkColor, borderColor: band.border } : { backgroundColor: band.color, borderColor: band.border }"
                    >
                        {{-- Band Header (Floating-style Label) --}}
                        <div
                            class="absolute left-6 text-xs font-black tracking-widest text-white rounded-lg shadow-sm z-10"
                            :style="{ backgroundColor: band.border }"
                            style="padding:12px !important;"
                        >
                            <span x-text="band.name"></span>
                        </div>

                        <div
                            class="min-h-[140px] rounded-lg border-2 border-dashed transition-colors gap-6 items-start content-start"
                            :class="{
                                    'border-primary-400 bg-primary-500/5': hoveredBand === idx,
                                    'border-[#4c566a]/30 dark:border-white/10': hoveredBand !== idx,
                                }"
                            style="display: grid !important; grid-template-columns: 1fr 1fr !important; gap: 1.5rem !important;"
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
                                <div
                                    class="w-full flex flex-col items-center justify-center py-6 text-[#4c566a] dark:text-gray-500 italic pointer-events-none opacity-60"
                                    style="grid-column: span 2 !important;">
                                    <x-filament::icon name="heroicon-m-arrow-down-tray"
                                                      class="w-8 h-8 mb-2"/>
                                    <span class="text-sm font-bold">Drop blocks here</span>
                                </div>
                            </template>

                            <template x-for="(block, blockIdx) in band.blocks" :key="block.id">
                                <div
                                    :draggable="true"
                                    x-on:dragstart="event.dataTransfer.setData('blockId', block.id); event.dataTransfer.setData('sourceBandIdx', idx);"
                                    x-on:click="openBlockModal(block)"
                                    class="group relative flex flex-col items-start bg-[#bf616a] dark:bg-[#bf616a] rounded-2xl cursor-grab active:cursor-grabbing hover:translate-y-[-4px] transition-all shadow-xl min-h-[100px]"
                                    :style="'padding:40px !important; width: 100% !important; border: 4px dotted rgba(255, 255, 255, 0.4) !important; justify-content: flex-start !important; grid-column: span ' + (block.position && block.position.width > 6 ? '2' : '1') + ' !important;'"
                                >
                                    <div class="flex items-center justify-between w-full mb-1">
                                        <x-filament::icon name="heroicon-m-bars-2"
                                                          class="w-6 h-6 text-white/90"/>
                                        <button
                                            type="button"
                                            x-on:click.stop="addBlockToAvailable(block.id, idx)"
                                            class="bg-black/20 hover:bg-black/40 rounded-lg text-white transition-colors shadow-inner p-1"
                                        >
                                            <x-filament::icon name="heroicon-m-x-mark" class="w-5 h-5"/>
                                        </button>
                                    </div>
                                    <span
                                        class="text-sm font-black text-white uppercase tracking-wider overflow-hidden text-ellipsis w-full"
                                        style="white-space: nowrap !important; align-self: flex-start !important;"
                                        x-html="block.label.replace(/ /g, '&nbsp;')"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Sidebar: Available Blocks (Right) - 25% width --}}
            <div class="sticky"
                 style="min-width: 0 !important; padding: 12px !important;">
                <div
                    class="bg-white dark:bg-gray-900 border-b-4 border-gray-200 dark:border-white/10 rounded-2xl shadow-xl overflow-hidden"
                    style="padding: 2px !important;">
                    <div class="border-b border-gray-100 dark:border-white/5 bg-gray-50 dark:bg-white/5">
                        <h3 class="font-black text-[#2e3440] dark:text-white flex items-center gap-3 uppercase tracking-wider">
                            <x-filament::icon name="heroicon-m-squares-plus" class="w-6 h-6 text-[#5e81ac]"/>
                            @lang('ip.available_blocks')
                        </h3>
                    </div>

                    <div class="bg-white dark:bg-gray-900">
                        <div class="grid grid-cols-1 gap-4">
                            <template x-for="block in blocks" :key="block.id">
                                <div
                                    class="group flex flex-col items-start gap-2 bg-[#5e81ac] dark:bg-[#5e81ac] border-b-4 border-[#435b7a] rounded-xl cursor-grab active:cursor-grabbing hover:brightness-110 transition-all shadow-lg min-h-[80px]"
                                    draggable="true"
                                    style="padding:12px !important; width: 100% !important; justify-content: flex-start !important;"
                                    x-on:dragstart="event.dataTransfer.setData('blockId', block.id); event.dataTransfer.setData('sourceBandIdx', 'available');"
                                >
                                    <div
                                        class="flex-shrink-0 w-8 h-8 flex items-center justify-center bg-white/20 rounded-lg text-white group-hover:bg-white/30 transition-colors">
                                        <x-filament::icon name="heroicon-m-plus" class="w-5 h-5"/>
                                    </div>
                                    <span
                                        class="text-sm font-black text-white uppercase tracking-tight overflow-hidden text-ellipsis w-full"
                                        style="white-space: nowrap !important; align-self: flex-start !important;"
                                        x-html="block.label.replace(/ /g, '&nbsp;')"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Block Configuration Modal --}}
    <div
        x-show="isModalOpen"
        class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
        x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        <div
            class="bg-white dark:bg-gray-900 w-full max-w-4xl rounded-3xl shadow-2xl overflow-hidden"
            @click.away="closeBlockModal()"
        >
            {{-- Modal Header --}}
            <div class="px-8 py-6 bg-[#527397] flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-black text-white uppercase tracking-wider flex items-center gap-3">
                        <x-filament::icon name="heroicon-m-cog-6-tooth" class="w-6 h-6"/>
                        Configure <span x-text="editingBlock?.label"></span>
                    </h3>
                </div>
                <button @click="closeBlockModal()" class="text-white/80 hover:text-white transition-colors">
                    <x-filament::icon name="heroicon-m-x-mark" class="w-8 h-8"/>
                </button>
            </div>

            {{-- Modal Body --}}
            <div class="p-8 grid grid-cols-2 gap-8">
                {{-- Left Side: Available Fields --}}
                <div>
                    <h4 class="text-xs font-black uppercase tracking-widest text-gray-500 mb-4">Available
                        Fields</h4>
                    <div class="grid grid-cols-1 gap-3 max-h-[400px] overflow-y-auto pr-2">
                        <template x-for="field in availableFields" :key="field.id">
                            <div
                                class="flex items-center justify-between p-3 bg-gray-50 dark:bg-white/5 rounded-xl border border-gray-200 dark:border-white/10 group hover:border-primary-500 transition-colors cursor-pointer"
                                @click="addFieldToBlock(field.id)"
                            >
                                    <span class="text-sm font-bold text-gray-700 dark:text-gray-300"
                                          x-text="field.label"></span>
                                <x-filament::icon name="heroicon-m-plus-circle"
                                                  class="w-5 h-5 text-gray-400 group-hover:text-primary-500"/>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Right Side: Active Fields (Drag & Drop Area) --}}
                <div
                    class="bg-gray-50 dark:bg-black/20 rounded-2xl border-2 border-dashed border-gray-300 dark:border-white/10 p-6 flex flex-col"
                >
                    <h4 class="text-xs font-black uppercase tracking-widest text-gray-500 mb-4">Active Fields
                        (Order)</h4>

                    <div class="flex-1 space-y-3 min-h-[200px]">
                        <template x-if="editingBlock?.config?.fields?.length === 0">
                            <div class="flex flex-col items-center justify-center h-full text-gray-400 italic">
                                <p class="text-sm">No fields added yet.</p>
                                <p class="text-xs mt-1">Click a field on the left to add it.</p>
                            </div>
                        </template>

                        <template x-for="(field, index) in editingBlock?.config?.fields" :key="field.id">
                            <div
                                class="flex items-center justify-between p-4 bg-[#5e81ac] text-white rounded-xl shadow-md border-b-4 border-[#435b7a]"
                            >
                                <div class="flex items-center gap-3">
                                    <div class="flex flex-col gap-1">
                                        <button @click="moveField(index, -1)" class="text-white/60 hover:text-white"
                                                :disabled="index === 0">
                                            <x-filament::icon name="heroicon-m-chevron-up" class="w-4 h-4"/>
                                        </button>
                                        <button @click="moveField(index, 1)" class="text-white/60 hover:text-white"
                                                :disabled="index === editingBlock.config.fields.length - 1">
                                            <x-filament::icon name="heroicon-m-chevron-down" class="w-4 h-4"/>
                                        </button>
                                    </div>
                                    <span class="text-sm font-black uppercase tracking-tight"
                                          x-text="field.label"></span>
                                </div>
                                <button @click="removeFieldFromBlock(field.id)"
                                        class="text-white/60 hover:text-white transition-colors">
                                    <x-filament::icon name="heroicon-m-minus-circle" class="w-5 h-5"/>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div
                class="px-8 py-6 bg-gray-50 dark:bg-white/5 border-t border-gray-100 dark:border-white/5 flex justify-end gap-3">
                <x-filament::button
                    color="gray"
                    @click="closeBlockModal()"
                >
                    Cancel
                </x-filament::button>
                <x-filament::button
                    color="primary"
                    @click="saveBlockModal()"
                    style="background-color: #ebcb8b !important; color: #2e3440 !important;"
                >
                    Save Configuration
                </x-filament::button>
            </div>
        </div>
    </div>
    </div>
</x-filament-panels::page>
