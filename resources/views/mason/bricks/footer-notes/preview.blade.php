@props([
    'config' => []
])

    <div style="display: block; width: 100%; min-height: 100px; border: 1px solid #999; padding: 12px; border-radius: 4px; background-color: #CCCCCC; font-size: 11px; color: #333; box-sizing: border-box;">
        <strong>{{ trans('ip.footer') }}</strong>
        @if(!empty($config['footer_content']))
            <div style="margin-top: 8px;">{{ $config['footer_content'] }}</div>
        @else
            <div style="margin-top: 8px; font-style: italic;">{{ trans('ip.footer_placeholder') }}</div>
        @endif
    </div>
