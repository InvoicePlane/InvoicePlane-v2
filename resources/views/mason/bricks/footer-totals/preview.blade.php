@props([
    'config' => []
])

<div class="border-2 border-dashed border-gray-300 p-4 rounded bg-white">
    <div style="text-align: {{ $config['text_align'] ?? 'right' }}; font-size: {{ $config['font_size'] ?? 10 }}pt;">
        <table class="w-full max-w-xs ml-auto">
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
