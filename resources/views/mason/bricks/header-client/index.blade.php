@props([
    'config' => [],
    'data' => []
])

<div class="client-header" style="font-size: {{ $config['font_size'] ?? 10 }}pt; text-align: {{ $config['text_align'] ?? 'right' }};">
    <strong>{{ trans('ip.bill_to') }}</strong><br>
    <strong>{{ $data['client']['name'] ?? '' }}</strong><br>
    @if($config['show_address'] ?? true)
        {{ $data['client']['address'] ?? '' }}<br>
        {{ $data['client']['city'] ?? '' }} {{ $data['client']['postal_code'] ?? '' }}<br>
    @endif
    @if($config['show_phone'] ?? true)
        {{ trans('ip.phone') }}: {{ $data['client']['phone'] ?? '' }}<br>
    @endif
    @if($config['show_email'] ?? true)
        {{ trans('ip.email') }}: {{ $data['client']['email'] ?? '' }}<br>
    @endif
</div>
