@props([
    'config' => []
])

    <div style="display: block; width: 100%; min-height: 100px; border: 1px solid #999; padding: 12px; border-radius: 4px; background-color: #CCCCCC; font-size: 11px; color: #333; box-sizing: border-box;">
        <strong>{{ trans('ip.bill_to') }}</strong>
        <br>{{ trans('ip.client_name') }}
        @if($config['show_address'] ?? true)<br>{{ trans('ip.client_address') }}@endif
        @if($config['show_phone'] ?? true)<br>{{ trans('ip.phone') }}@endif
        @if($config['show_email'] ?? true)<br>{{ trans('ip.email') }}@endif
    </div>
