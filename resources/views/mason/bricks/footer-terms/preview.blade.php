<div class="bg-white p-4 border-2 border-dashed border-gray-300 rounded" style="font-size: {{ $config['font_size'] ?? 8 }}pt;">
    <div class="font-bold mb-2">{{ trans('ip.terms_conditions') }}</div>
    <div class="text-gray-600">
        @if(!empty($config['terms_content']))
            {{ $config['terms_content'] }}
        @else
            <p class="text-sm italic">{{ trans('ip.terms_placeholder') }}</p>
        @endif
    </div>

</div>
</div>
