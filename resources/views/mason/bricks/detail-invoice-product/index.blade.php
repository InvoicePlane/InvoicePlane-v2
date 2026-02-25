@props([
    'config' => [],
    'data' => []
])

<div class="invoice-product-items" style="font-size: {{ $config['font_size'] ?? 9 }}pt;">
    <table width="100%" cellpadding="4" cellspacing="0" border="1" style="border-collapse: collapse;">
        <thead>
            <tr style="background-color: #f3f4f6;">
                @if($config['show_sku'] ?? true)
                    <th align="left" width="12%">{{ trans('ip.sku') }}</th>
                @endif
                @if($config['show_description'] ?? true)
                    <th align="left">{{ trans('ip.description') }}</th>
                @endif
                @if($config['show_quantity'] ?? true)
                    <th align="center" width="10%">{{ trans('ip.quantity') }}</th>
                @endif
                @if($config['show_unit_price'] ?? true)
                    <th align="right" width="12%">{{ trans('ip.unit_price') }}</th>
                @endif
                @if($config['show_tax'] ?? true)
                    <th align="right" width="10%">{{ trans('ip.tax') }}</th>
                @endif
                @if($config['show_discount'] ?? false)
                    <th align="right" width="10%">{{ trans('ip.discount') }}</th>
                @endif
                @if($config['show_total'] ?? true)
                    <th align="right" width="12%">{{ trans('ip.total') }}</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach(($data['invoice_items'] ?? []) as $index => $item)
                <tr style="{{ ($config['alternating_rows'] ?? true) && $index % 2 == 1 ? 'background-color: #f9fafb;' : '' }}">
                    @if($config['show_sku'] ?? true)
                        <td>{{ $item['sku'] ?? '' }}</td>
                    @endif
                    @if($config['show_description'] ?? true)
                        <td>{{ $item['description'] ?? '' }}</td>
                    @endif
                    @if($config['show_quantity'] ?? true)
                        <td align="center">{{ $item['quantity'] ?? 0 }}</td>
                    @endif
                    @if($config['show_unit_price'] ?? true)
                        <td align="right">{{ $item['unit_price'] ?? '0.00' }}</td>
                    @endif
                    @if($config['show_tax'] ?? true)
                        <td align="right">{{ $item['tax'] ?? '0.00' }}</td>
                    @endif
                    @if($config['show_discount'] ?? false)
                        <td align="right">{{ $item['discount'] ?? '0.00' }}</td>
                    @endif
                    @if($config['show_total'] ?? true)
                        <td align="right">{{ $item['total'] ?? '0.00' }}</td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
