@props([
    'config' => [],
    'data' => []
])

<div class="totals-section" style="font-size: {{ $config['font_size'] ?? 10 }}pt; text-align: {{ $config['text_align'] ?? 'right' }};">
    <table width="40%" align="right" cellpadding="4" cellspacing="0">
        @if($config['show_subtotal'] ?? true)
            <tr>
                <td align="right"><strong>{{ trans('ip.subtotal') }}:</strong></td>
                <td align="right" width="30%">{{ $data['totals']['subtotal'] ?? '0.00' }}</td>
            </tr>
        @endif
        @if($config['show_tax'] ?? true)
            <tr>
                <td align="right"><strong>{{ trans('ip.tax') }}:</strong></td>
                <td align="right">{{ $data['totals']['tax'] ?? '0.00' }}</td>
            </tr>
        @endif
        @if($config['show_total'] ?? true)
            <tr style="{{ ($config['highlight_total'] ?? true) ? 'background-color: #f3f4f6; font-weight: bold;' : '' }}">
                <td align="right" style="padding: 8px; font-size: {{ ($config['font_size'] ?? 10) + 2 }}pt;"><strong>{{ trans('ip.total') }}:</strong></td>
                <td align="right" style="padding: 8px; font-size: {{ ($config['font_size'] ?? 10) + 2 }}pt;"><strong>{{ $data['totals']['total'] ?? '0.00' }}</strong></td>
            </tr>
        @endif
        @if(($config['show_paid'] ?? false) && isset($data['totals']['paid']))
            <tr>
                <td align="right"><strong>{{ trans('ip.paid') }}:</strong></td>
                <td align="right">{{ $data['totals']['paid'] }}</td>
            </tr>
        @endif
        @if(($config['show_balance'] ?? false) && isset($data['totals']['balance']))
            <tr style="font-weight: bold;">
                <td align="right">{{ trans('ip.balance_due') }}:</td>
                <td align="right">{{ $data['totals']['balance'] }}</td>
            </tr>
        @endif
    </table>
</div>
