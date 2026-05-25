<div style="font-size: {{ $config['font_size'] ?? 10 }}pt; text-align: {{ $config['text_align'] ?? 'right' }};">
    @if($config['show_quote_number'] ?? true)
        <div><strong>{{ trans('ip.quote_number') }}:</strong> {{ $data['quote']['quote_number'] ?? '' }}</div>
    @endif
    @if($config['show_quoted_at'] ?? true)
        <div><strong>{{ trans('ip.quoted_at') }}:</strong> {{ $data['quote']['quoted_at'] ?? '' }}</div>
    @endif
    @if($config['show_expires_at'] ?? true)
        <div><strong>{{ trans('ip.expires_at') }}:</strong> {{ $data['quote']['quote_expires_at'] ?? '' }}</div>
    @endif
    @if($config['show_status'] ?? true)
        <div><strong>{{ trans('ip.status') }}:</strong> {{ $data['quote']['quote_status'] ?? '' }}</div>
    @endif
</div>
