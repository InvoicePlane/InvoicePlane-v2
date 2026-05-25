<div class="bg-white p-4 border-2 border-dashed border-gray-300 rounded" style="font-size: {{ $config['font_size'] ?? 9 }}pt;">
    <div class="font-bold mb-2">{{ trans('ip.summary') }}</div>
    <div class="text-gray-600">
        @if(!empty($config['summary_content']))
            {{ $config['summary_content'] }}
        @else
            <p class="text-sm italic">{{ trans('ip.summary_placeholder') }}</p>
        @endif
    </div>
</div>
