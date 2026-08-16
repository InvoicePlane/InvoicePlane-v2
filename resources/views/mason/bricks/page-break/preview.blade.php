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

<div class="border-t-2 border-dashed border-gray-400 relative my-4">
    <span class="absolute left-1/2 -translate-x-1/2 -top-3 bg-white px-2 text-xs text-gray-500 uppercase tracking-wide">
        {{ trans('ip.page_break') }}
    </span>

</div>
</div>
