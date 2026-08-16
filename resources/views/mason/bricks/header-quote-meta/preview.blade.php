<div class="bg-white p-4 border-2 border-dashed border-gray-300 rounded" style="font-size: {{ $config['font_size'] ?? 10 }}pt; text-align: {{ $config['text_align'] ?? 'right' }};">
    <div class="font-bold text-lg mb-2">{{ trans('ip.quote_metadata') }}</div>
    <div class="space-y-1 text-gray-600">
        @if($config['show_quote_number'] ?? true)
            <div><strong>{{ trans('ip.quote_number') }}:</strong> QUO-001</div>
        @endif
        @if($config['show_quoted_at'] ?? true)
            <div><strong>{{ trans('ip.quoted_at') }}:</strong> {{ now()->format('Y-m-d') }}</div>
        @endif
        @if($config['show_expires_at'] ?? true)
            <div><strong>{{ trans('ip.expires_at') }}:</strong> {{ now()->addDays(30)->format('Y-m-d') }}</div>
        @endif
        @if($config['show_status'] ?? true)
            <div><strong>{{ trans('ip.status') }}:</strong> {{ trans('ip.draft') }}</div>
        @endif
    </div>

</div>
</div>
