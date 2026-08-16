<div class="bg-white p-4 border-2 border-dashed border-gray-300 rounded" style="font-size: {{ $config['font_size'] ?? 10 }}pt; text-align: {{ $config['text_align'] ?? 'left' }};">
    <div class="font-bold text-lg mb-2">{{ trans('ip.project_header') }}</div>
    <div class="space-y-1 text-gray-600">
        @if($config['show_project_number'] ?? true)
            <div><strong>{{ trans('ip.project_number') }}:</strong> PROJECT-001</div>
        @endif
        @if($config['show_project_name'] ?? true)
            <div><strong>{{ trans('ip.project_name') }}:</strong> Sample Project</div>
        @endif
        @if($config['show_start_date'] ?? true)
            <div><strong>{{ trans('ip.start_date') }}:</strong> {{ now()->format('Y-m-d') }}</div>
        @endif
        @if($config['show_end_date'] ?? true)
            <div><strong>{{ trans('ip.end_date') }}:</strong> {{ now()->addDays(30)->format('Y-m-d') }}</div>
        @endif
        @if($config['show_status'] ?? true)
            <div><strong>{{ trans('ip.status') }}:</strong> {{ trans('ip.in_progress') }}</div>
        @endif
    </div>

</div>
</div>
