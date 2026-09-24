@props([
    'config' => [],
])

<div class="column-labels-preview" style="font-size: {{ $config['font_size'] ?? 9 }}pt;">
    <table width="100%" cellpadding="2" cellspacing="0" style="border-collapse: collapse; background: #f9fafb; border: 1px solid #e5e7eb;">
        <thead>
            <tr style="background-color: #f3f4f6; font-size: 8pt;">
                @if($config['show_sku'] ?? false)
                    <th align="left" style="padding: 2px 4px; font-weight: bold;">{{ trans('ip.sku') }}</th>
                @endif
                @if($config['show_description'] ?? true)
                    <th align="left" style="padding: 2px 4px; font-weight: bold;">{{ trans('ip.description') }}</th>
                @endif
                @if($config['show_quantity'] ?? true)
                    <th align="center" style="padding: 2px 4px; font-weight: bold;">{{ trans('ip.quantity') }}</th>
                @endif
                @if($config['show_price'] ?? true)
                    <th align="right" style="padding: 2px 4px; font-weight: bold;">{{ trans('ip.price') }}</th>
                @endif
                @if($config['show_tax'] ?? true)
                    <th align="right" style="padding: 2px 4px; font-weight: bold;">{{ trans('ip.tax') }}</th>
                @endif
                @if($config['show_discount'] ?? false)
                    <th align="right" style="padding: 2px 4px; font-weight: bold;">{{ trans('ip.discount') }}</th>
                @endif
                @if($config['show_total'] ?? true)
                    <th align="right" style="padding: 2px 4px; font-weight: bold;">{{ trans('ip.total') }}</th>
                @endif
            </tr>
        </thead>
    </table>
</div>
