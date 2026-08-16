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
    <div class="border-2 border-dashed border-gray-300 rounded bg-gray-50 flex items-center justify-center text-xs text-gray-500"
     style="height: {{ min((int) ($config['height'] ?? 20), 100) }}px;" h-full">
    {{ trans('ip.spacer') }} — {{ (int) ($config['height'] ?? 20) }}px

</div>
</div>
