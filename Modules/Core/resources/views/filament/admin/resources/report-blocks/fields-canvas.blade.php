@php
    use Modules\Core\Services\ReportFieldService;

    // Load available fields from service
    $fieldService = app(ReportFieldService::class);
    $availableFields = $fieldService->getAvailableFields();
@endphp

<div class="w-full" x-data="initFieldsCanvas()">
    {{-- Hidden input to store fields_canvas data for form submission --}}
    <input type="hidden" name="data[fields_canvas]" value="[]"/>

    <div class="grid grid-cols-12 gap-4">
        {{-- Available Fields Sidebar --}}
        <div class="col-span-3">
            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                <h4 class="text-sm font-semibold mb-3 text-gray-700 dark:text-gray-300">@lang('ip.available_fields')</h4>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">
                    Drag fields to the canvas to configure block layout
                </p>
                <div class="space-y-2 max-h-96 overflow-y-auto">
                    @foreach ($availableFields as $field)
                        <div
                            draggable="true"
                            @dragstart="event.dataTransfer.setData('fieldId', '{{ $field['id'] }}')"
                            class="bg-blue-500 text-white rounded px-3 py-2 text-sm cursor-grab active:cursor-grabbing hover:bg-blue-600 transition-colors"
                        >
                            <div class="font-medium">{{ $field['label'] }}</div>
                            <div class="text-xs opacity-75">Source: {{ $field['source'] ?? 'Custom' }}</div>
                        </div>
                    @endforeach
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
                <div x-show="canvasFields.length === 0"
                     class="flex items-center justify-center h-full text-gray-400 dark:text-gray-600">
                    <div class="text-center">
                        <svg class="w-16 h-16 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                        <p class="text-sm font-medium">Drag fields here to configure block layout</p>
                    </div>
                </div>

                <div x-show="canvasFields.length > 0" class="space-y-2">
                    <template x-for="(field, index) in canvasFields" :key="field.id">
                        <div
                            class="bg-green-500 text-white rounded px-3 py-2 text-sm cursor-move shadow-lg flex items-center justify-between group"
                            style="position: relative; margin-bottom: 0.5rem;"
                        >
                            <span x-text="field.label"></span>
                            <button
                                type="button"
                                @click.stop="removeFieldFromCanvas(field.id)"
                                class="ml-2 bg-red-500 rounded-full w-5 h-5 flex items-center justify-center hover:bg-red-600 transition-colors"
                                title="Remove field"
                            >
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </template>
                </div>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                Fields will be saved to the block configuration when you save the block.
            </p>
        </div>
    </div>
</div>

<script>
    function initFieldsCanvas() {
        return {
            canvasFields: [],
            availableFields: @json($availableFields),

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
                    this.updateFormField();
                }
            },

            removeFieldFromCanvas(fieldId) {
                this.canvasFields = this.canvasFields.filter(f => f.id !== fieldId);
                this.updateFormField();
            },

            updateFormField() {
                const hiddenInput = document.querySelector('input[name="data[fields_canvas]"]');
                if (hiddenInput) {
                    hiddenInput.value = JSON.stringify(this.canvasFields);
                    hiddenInput.dispatchEvent(new Event('change', {bubbles: true}));
                }
            }
        }
    }
</script>

