@props([
    'config' => [],
    'data' => []
])

<div class="line-items-column-labels" style="font-size: {{ $config['font_size'] ?? 9 }}pt;">
    <table width="100%" cellpadding="4" cellspacing="0" border="1" style="border-collapse: collapse;">
        <thead>
            <tr style="background-color: #f3f4f6;">
                @if($config['show_sku'] ?? false)
                    <th align="left" width="12%">{{ trans('ip.sku') }}</th>
                @endif
                @if($config['show_description'] ?? true)
                    <th align="left">{{ trans('ip.description') }}</th>
                @endif
                @if($config['show_quantity'] ?? true)
                    <th align="center" width="10%">{{ trans('ip.quantity') }}</th>
                @endif
                @if($config['show_price'] ?? true)
                    <th align="right" width="{{ ($config['show_sku'] ?? false) ? '12%' : '15%' }}">{{ trans('ip.price') }}</th>
                @endif
                @if($config['show_tax'] ?? true)
                    <th align="right" width="10%">{{ trans('ip.tax') }}</th>
                @endif
                @if($config['show_discount'] ?? false)
                    <th align="right" width="10%">{{ trans('ip.discount') }}</th>
                @endif
                @if($config['show_total'] ?? true)
                    <th align="right" width="{{ ($config['show_sku'] ?? false) ? '12%' : '15%' }}">{{ trans('ip.total') }}</th>
                @endif
            </tr>
        </thead>
    </table>
</div>
