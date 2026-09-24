@props([
    'config' => []
])

    <div style="display: block; width: 100%; min-height: 100px; border: 1px solid #999; padding: 12px; border-radius: 4px; background-color: #CCCCCC; font-size: 11px; color: #333; box-sizing: border-box;">
        <strong>{{ trans('ip.quote_metadata') }}</strong>
        @if($config['show_quote_number'] ?? true)<br>{{ trans('ip.quote_number') }}: {{ 'QUO-001' }}@endif
        @if($config['show_quoted_at'] ?? true)<br>{{ trans('ip.quoted_at') }}: {{ now()->format('Y-m-d') }}@endif
        @if($config['show_expires_at'] ?? true)<br>{{ trans('ip.expires_at') }}: {{ now()->addDays(30)->format('Y-m-d') }}@endif
        @if($config['show_status'] ?? true)<br>{{ trans('ip.status') }}: {{ trans('ip.draft') }}@endif
    </div>
