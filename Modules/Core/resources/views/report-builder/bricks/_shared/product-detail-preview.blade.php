{{-- Shared picker-thumbnail preview for the per-document-type product detail
     bricks (DetailInvoiceProductBrick, DetailQuoteProductBrick). Their preview
     markup is identical — only the id/data key differ, and neither is needed
     for a static thumbnail. --}}
@props([
    'config' => [],
])

    <div style="display: block; width: 100%; min-height: 100px; border: 1px solid #999; padding: 12px; border-radius: 4px; background-color: #CCCCCC; font-size: 10px; color: #333; box-sizing: border-box;">
        <table style="width: 100%; margin-top: 2px; font-size: {{ $config['font_size'] ?? 9 }}pt;">
            <tr style="border-bottom: 1px solid #999;">
                @if($config['show_sku'] ?? true)<th style="text-align: left; padding: 2px; font-weight: bold;">{{ trans('ip.sku') }}</th>@endif
                @if($config['show_description'] ?? true)<th style="text-align: left; padding: 2px; font-weight: bold;">{{ trans('ip.description') }}</th>@endif
                @if($config['show_quantity'] ?? true)<th style="text-align: center; padding: 2px; font-weight: bold;">{{ trans('ip.quantity') }}</th>@endif
                @if($config['show_unit_price'] ?? true)<th style="text-align: right; padding: 2px; font-weight: bold;">{{ trans('ip.unit_price') }}</th>@endif
                @if($config['show_tax'] ?? true)<th style="text-align: right; padding: 2px; font-weight: bold;">{{ trans('ip.tax') }}</th>@endif
                @if($config['show_discount'] ?? false)<th style="text-align: right; padding: 2px; font-weight: bold;">{{ trans('ip.discount') }}</th>@endif
                @if($config['show_total'] ?? true)<th style="text-align: right; padding: 2px; font-weight: bold;">{{ trans('ip.total') }}</th>@endif
            </tr>
        </table>
    </div>
