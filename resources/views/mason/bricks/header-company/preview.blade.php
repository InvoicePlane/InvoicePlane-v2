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
        <strong>{{ trans('ip.company_name') }}</strong>
        @if($config['show_address'] ?? true)<br>{{ trans('ip.company_address') }}@endif
        @if($config['show_phone'] ?? true)<br>{{ trans('ip.phone') }}@endif
        @if($config['show_email'] ?? true)<br>{{ trans('ip.email') }}@endif
        @if($config['show_vat_id'] ?? true)<br>{{ trans('ip.vat_id') }}@endif
    </div>
</div>
