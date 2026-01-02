@php
    // Load available fields from config grouped by data source
    $availableFieldsConfig = config('report-fields');
    $availableFields = [];
    
    // Flatten all fields from all data sources
    foreach ($availableFieldsConfig as $source => $fields) {
        foreach ($fields as $field) {
            $availableFields[] = [
                'id' => $field['id'],
                'label' => $field['label'],
                'source' => $source,
                'format' => $field['format'] ?? null,
            ];
        }
    }

    // Load existing canvas fields from Livewire state if available
    $initialCanvasFields = [];

    if (isset($this) && property_exists($this, 'data')) {
        $initialCanvasFields = (array) data_get($this->data, 'fields', []);
    }
@endphp

<div x-data="{
    canvasFields: @js($initialCanvasFields),
    availableFields: @js($availableFields),
    init() {
        // Ensure canvasFields is an array; it is pre-populated from wire state if available
        if (!Array.isArray(this.canvasFields)) {
            this.canvasFields = [];
        }
    },
    addFieldToCanvas(fieldId) {
        const field = this.availableFields.find(f => f.id === fieldId);
        if (field && !this.canvasFields.find(f => f.id === fieldId)) {
            this.canvasFields.push({
                id: field.id,
                label: field.label,
                x: 0,
                y: this.canvasFields.length * 50,
                width: 200,
                height: 40
            });
            this.syncToWire();
        }
    },
    removeFieldFromCanvas(fieldId) {
        this.canvasFields = this.canvasFields.filter(f => f.id !== fieldId);
        this.syncToWire();
    },
    syncToWire() {
        // Sync to Livewire state
        @this.set('data.fields', this.canvasFields);
    }
}" class="w-full">
    <div class="grid grid-cols-12 gap-4">
        {{-- Available Fields Sidebar --}}
        <div class="col-span-3">
            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                <h4 class="text-sm font-semibold mb-3 text-gray-700 dark:text-gray-300">Available Fields</h4>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">
                    Drag fields to the canvas to configure block layout
                </p>
                <div class="space-y-2 max-h-96 overflow-y-auto">
                    <template x-for="field in availableFields" :key="field.id">
                        <div
                            draggable="true"
                            @dragstart="event.dataTransfer.setData('fieldId', field.id)"
                            class="bg-blue-500 text-white rounded px-3 py-2 text-sm cursor-grab active:cursor-grabbing hover:bg-blue-600 transition-colors"
                        >
                            <div class="font-medium" x-text="field.label"></div>
                            <div class="text-xs opacity-75" x-text="'Source: ' + field.source"></div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        {{-- Canvas Area --}}
        <div class="col-span-9">
            <div
                class="bg-white dark:bg-gray-900 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg min-h-[400px] p-4 relative"
                @dragover.prevent
                @drop.prevent="
                    const fieldId = event.dataTransfer.getData('fieldId');
                    if (fieldId) {
                        addFieldToCanvas(fieldId);
                    }
                "
            >
                <template x-if="canvasFields.length === 0">
                    <div class="flex items-center justify-center h-full text-gray-400 dark:text-gray-600">
                        <div class="text-center">
                            <svg class="w-16 h-16 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                            </svg>
                            <p class="text-sm font-medium">Drag fields here to configure block layout</p>
                        </div>
                    </div>
                </template>

                <template x-for="(field, index) in canvasFields" :key="field.id">
                    <div
                        class="absolute bg-green-500 text-white rounded px-3 py-2 text-sm cursor-move shadow-lg flex items-center justify-between group"
                        :style="`left: ${field.x}px; top: ${field.y}px; width: ${field.width}px; height: ${field.height}px;`"
                    >
                        <span x-text="field.label"></span>
                        <button
                            type="button"
                            @click.stop="removeFieldFromCanvas(field.id)"
                            class="ml-2 opacity-0 group-hover:opacity-100 transition-opacity bg-red-500 rounded-full w-5 h-5 flex items-center justify-center hover:bg-red-600"
                        >
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </template>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                Fields will be saved to a JSON file when you save the block configuration.
            </p>
        </div>
    </div>
</div>
