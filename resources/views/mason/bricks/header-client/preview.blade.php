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
