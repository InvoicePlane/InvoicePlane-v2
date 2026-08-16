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
@endphp

<div style="display: inline-block; vertical-align: top; width: {{ $widthValue }}; padding-right: 8px; box-sizing: border-box;">
    <div style="display: block; width: 100%; height: {{ min((int) ($config['height'] ?? 20), 100) }}px; border: 1px dashed #999; border-radius: 4px; background-color: #CCCCCC; display: flex; align-items: center; justify-content: center; font-size: 10px; color: #666; box-sizing: border-box;">
        {{ trans('ip.spacer') }} — {{ (int) ($config['height'] ?? 20) }}px
    </div>
</div>
