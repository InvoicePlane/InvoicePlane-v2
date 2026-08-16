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

<div class="{{ $width }} float-left">
    <div class="border-2 border-dashed border-gray-300 p-4 rounded bg-white h-full">
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
