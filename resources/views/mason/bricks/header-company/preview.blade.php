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

<div class="{{ $width }} float-left p-2" style="box-sizing: border-box;">
    <div class="border-2 border-dashed border-gray-400 p-3 rounded bg-gray-100 h-full" style="min-height: 120px;">
    <div class="flex items-start gap-4">
        @if($config['show_logo'] ?? true)
            <div class="w-16 h-16 bg-gray-200 rounded flex items-center justify-center flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 21h18"/><path d="M9 8h1"/><path d="M9 12h1"/><path d="M9 16h1"/><path d="M14 8h1"/><path d="M14 12h1"/><path d="M14 16h1"/><path d="M6 4h12v17H6z"/>
                </svg>
            </div>
        @endif
        <div class="flex-1" style="text-align: {{ $config['text_align'] ?? 'left' }}; font-size: {{ $config['font_size'] ?? 10 }}pt; font-weight: {{ $config['font_weight'] ?? 'bold' }};">
            <h3 class="font-bold text-lg mb-1">{{ trans('ip.company_name') }}</h3>
            @if($config['show_address'] ?? true)
                <p class="text-sm text-gray-600">{{ trans('ip.company_address') }}</p>
            @endif
            @if($config['show_phone'] ?? true)
                <p class="text-sm text-gray-600">{{ trans('ip.phone') }}: +1 234 567 890</p>
            @endif
            @if($config['show_email'] ?? true)
                <p class="text-sm text-gray-600">{{ trans('ip.email') }}: info@company.com</p>
            @endif
            @if($config['show_vat_id'] ?? true)
                <p class="text-sm text-gray-600">{{ trans('ip.vat_id') }}: 12345678</p>
            @endif
        </div>
    </div>
    </div>
</div>
