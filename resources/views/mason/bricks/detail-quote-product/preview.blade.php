@props([
    'config' => []
])

@php
$width = match($config['_width'] ?? 'full') {
    'one_third' => 'w-1/3',
    'half' => 'w-1/2',
    'two_thirds' => 'w-2/3',
    default => 'w-full',
};
@endphp

<div class="{{ $width }} inline-block align-top">
    <div class="border-2 border-dashed border-gray-300 p-4 rounded bg-white h-full">
    <table class="w-full text-sm" style="font-size: {{ $config['font_size'] ?? 9 }}pt;">
        <thead class="bg-gray-100">
            <tr>
                @if($config['show_sku'] ?? true)
                    <th class="text-left p-2 border-b">{{ trans('ip.sku') }}</th>
                @endif
                @if($config['show_description'] ?? true)
                    <th class="text-left p-2 border-b">{{ trans('ip.description') }}</th>
                @endif
                @if($config['show_quantity'] ?? true)
                    <th class="text-center p-2 border-b">{{ trans('ip.quantity') }}</th>
                @endif
                @if($config['show_unit_price'] ?? true)
                    <th class="text-right p-2 border-b">{{ trans('ip.unit_price') }}</th>
                @endif
                @if($config['show_tax'] ?? true)
                    <th class="text-right p-2 border-b">{{ trans('ip.tax') }}</th>
                @endif
                @if($config['show_discount'] ?? false)
                    <th class="text-right p-2 border-b">{{ trans('ip.discount') }}</th>
                @endif
                @if($config['show_total'] ?? true)
                    <th class="text-right p-2 border-b">{{ trans('ip.total') }}</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @for($i = 1; $i <= 3; $i++)
                <tr class="{{ ($config['alternating_rows'] ?? true) && $i % 2 == 0 ? 'bg-gray-50' : '' }}">
                    @if($config['show_sku'] ?? true)
                        <td class="p-2">SKU-{{ str_pad($i, 3, '0', STR_PAD_LEFT) }}</td>
                    @endif
                    @if($config['show_description'] ?? true)
                        <td class="p-2">{{ trans('ip.product') }} {{ $i }}</td>
                    @endif
                    @if($config['show_quantity'] ?? true)
                        <td class="text-center p-2">{{ $i }}</td>
                    @endif
                    @if($config['show_unit_price'] ?? true)
                        <td class="text-right p-2">$100.00</td>
                    @endif
                    @if($config['show_tax'] ?? true)
                        <td class="text-right p-2">$10.00</td>
                    @endif
                    @if($config['show_discount'] ?? false)
                        <td class="text-right p-2">$0.00</td>
                    @endif
                    @if($config['show_total'] ?? true)
                        <td class="text-right p-2">$110.00</td>
                    @endif
                </tr>
            @endfor
        </tbody>
    </table>

</div>
</div>
