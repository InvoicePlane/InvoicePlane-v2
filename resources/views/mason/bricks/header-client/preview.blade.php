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
