@props([
    'config' => []
])

@php
$widthValue = match($config['_width'] ?? 'full') {
    'one_third' => '33.33%',
    'half' => '50%',
    'two_thirds' => '66.66%',
    default => '100%'
};
$bgColor = match($config['_width'] ?? 'full') {
    'one_third' => '#fcd34d',
    'half' => '#86efac',
    'two_thirds' => '#93c5fd',
    default => '#fca5a5'
};
@endphp

<div style="float: left; padding: 8px; box-sizing: border-box; width: {{ $widthValue }};">
    <div style="border: 3px solid black; padding: 12px; border-radius: 6px; background-color: {{ $bgColor }}; min-height: 120px; height: 100%;">
    CLIENT [{{ $config['_width'] ?? 'NONE' }}]
    <div style="text-align: {{ $config['text_align'] ?? 'right' }}; font-size: {{ $config['font_size'] ?? 10 }}pt;">
        <h3 class="font-semibold text-base mb-2">{{ trans('ip.bill_to') }}</h3>
        <p class="text-gray-700">{{ trans('ip.client_name') }}</p>
        @if($config['show_address'] ?? true)
            <p class="text-sm text-gray-600">{{ trans('ip.client_address') }}</p>
        @endif
        @if($config['show_phone'] ?? true)
            <p class="text-sm text-gray-600">{{ trans('ip.phone') }}: +1 555 123 4567</p>
        @endif
        @if($config['show_email'] ?? true)
            <p class="text-sm text-gray-600">{{ trans('ip.email') }}: client@example.com</p>
        @endif
    </div>
    </div>
</div>
