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
    <div style="display: block; width: 100%; min-height: 100px; border: 1px solid #999; padding: 12px; border-radius: 4px; background-color: #CCCCCC; font-size: 11px; color: #333; box-sizing: border-box;">
        <strong>{{ trans('ip.footer') }}</strong>
        @if(!empty($config['footer_content']))
            <div style="margin-top: 8px;">{{ $config['footer_content'] }}</div>
        @else
            <div style="margin-top: 8px; font-style: italic;">{{ trans('ip.footer_placeholder') }}</div>
        @endif
    </div>
</div>
