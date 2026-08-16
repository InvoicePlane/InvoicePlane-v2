@props([
    'config' => []
])

@php
$width = match($config['_width'] ?? 'full') {
    'one_third' => 'w-1/3',
    'half' => 'w-1/2',
    'two_thirds' => 'w-2/3',
    default => 'w-full',
};
@endphp

<div class="{{ $width }}" style="float: left; padding: 8px; box-sizing: border-box;">
    <div style="border: 2px dashed #9ca3af; padding: 12px; border-radius: 6px; background-color: #f3f4f6; min-height: 120px; height: 100%;">
    <div style="font-size: {{ $config['font_size'] ?? 8 }}pt;" class="text-gray-600">
        @if(!empty($config['footer_content']))
            <div class="prose prose-sm max-w-none">
                {{ $config['footer_content'] }}
            </div>
        @else
            <p class="text-sm italic">{{ trans('ip.footer_placeholder') }}</p>
        @endif
    </div>

</div>
</div>
