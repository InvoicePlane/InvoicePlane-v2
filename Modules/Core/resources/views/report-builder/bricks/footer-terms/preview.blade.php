@props([
    'config' => []
])

    <div style="display: block; width: 100%; min-height: 100px; border: 1px solid #999; padding: 12px; border-radius: 4px; background-color: #CCCCCC; font-size: 11px; color: #333; box-sizing: border-box;">
        <strong>{{ trans('ip.terms_conditions') }}</strong>
        @if(!empty($config['terms_content']))
            <div style="margin-top: 8px;">{!! $config['terms_content'] !!}</div>
        @else
            <div style="margin-top: 8px; font-style: italic;">{{ trans('ip.terms_placeholder') }}</div>
        @endif
    </div>
