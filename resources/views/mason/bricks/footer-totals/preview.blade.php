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
        <strong>{{ trans('ip.totals') }}</strong>
        <table style="width: 100%; margin-top: 6px; font-size: 10px;">
            @if($config['show_subtotal'] ?? true)
                <tr>
                    <td class="p-1 font-semibold">{{ trans('ip.subtotal') }}:</td>
                    <td class="p-1 text-right">$300.00</td>
                </tr>
            @endif
            @if($config['show_tax'] ?? true)
                <tr>
                    <td class="p-1 font-semibold">{{ trans('ip.tax') }}:</td>
                    <td class="p-1 text-right">$30.00</td>
                </tr>
            @endif
            @if($config['show_total'] ?? true)
                <tr class="{{ ($config['highlight_total'] ?? true) ? 'bg-gray-100 font-bold' : '' }}">
                    <td class="p-2 font-bold text-lg">{{ trans('ip.total') }}:</td>
                    <td class="p-2 text-right font-bold text-lg">$330.00</td>
                </tr>
            @endif
            @if($config['show_paid'] ?? false)
                <tr>
                    <td class="p-1 font-semibold">{{ trans('ip.paid') }}:</td>
                    <td class="p-1 text-right">$0.00</td>
                </tr>
            @endif
            @if($config['show_balance'] ?? false)
                <tr class="font-bold">
                    <td class="p-1">{{ trans('ip.balance_due') }}:</td>
                    <td class="p-1 text-right">$330.00</td>
                </tr>
            @endif
        </table>
    </div>
</div>
