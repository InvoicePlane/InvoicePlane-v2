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

<div style="float: left; padding: 8px; box-sizing: border-box; width: {{ match($config['_width'] ?? 'full') { 'one_third' => '33.33%', 'half' => '50%', 'two_thirds' => '66.66%', default => '100%' } }};">
    <div style="border: 2px dashed #9ca3af; padding: 12px; border-radius: 6px; background-color: #ff0000 !important; min-height: 120px; height: 100%;">
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
