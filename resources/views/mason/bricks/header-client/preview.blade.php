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

<div style="display: inline-block; vertical-align: top; padding: 4px; box-sizing: border-box; width: {{ $widthValue }};">
    <div style="border: 1px solid #999; padding: 12px; border-radius: 4px; background-color: #CCCCCC; min-height: 100px; font-size: 11px; color: #333;">
        <strong>{{ trans('ip.bill_to') }}</strong>
        <br>{{ trans('ip.client_name') }}
        @if($config['show_address'] ?? true)<br>{{ trans('ip.client_address') }}@endif
        @if($config['show_phone'] ?? true)<br>{{ trans('ip.phone') }}@endif
        @if($config['show_email'] ?? true)<br>{{ trans('ip.email') }}@endif
    </div>
</div>
