{{-- Shared picker-thumbnail preview for the per-document-type product detail
     bricks (DetailInvoiceProductBrick, DetailQuoteProductBrick). Their preview
     markup is identical — only the id/data key differ, and neither is needed
     for a static thumbnail. --}}
@props([
    'config' => [],
])

@php
    $placement = $config['description_placement'] ?? (isset($config['show_description']) ? ($config['show_description'] ? 'inline_column' : 'hidden') : 'inline_column');
    $columnCount = 0;
    if ($config['show_sku'] ?? true) {
        $columnCount++;
    }
    if ($placement === 'inline_column') {
        $columnCount++;
    }
    if ($config['show_quantity'] ?? true) {
        $columnCount++;
    }
    if ($config['show_unit_price'] ?? true) {
        $columnCount++;
    }
    if ($config['show_tax'] ?? true) {
        $columnCount++;
    }
    if ($config['show_discount'] ?? false) {
        $columnCount++;
    }
    if ($config['show_total'] ?? true) {
        $columnCount++;
    }
    $columnCount = max(1, $columnCount);
@endphp

    <div style="display: block; width: 100%; min-height: 100px; border: 1px solid #999; padding: 12px; border-radius: 4px; background-color: #CCCCCC; font-size: 10px; color: #333; box-sizing: border-box;">
        <table style="width: 100%; margin-top: 2px; font-size: {{ $config['font_size'] ?? 9 }}pt;">
            <tr style="border-bottom: 1px solid #999;">
                @if($config['show_sku'] ?? true)<th style="text-align: left; padding: 2px; font-weight: bold;">{{ trans('ip.sku') }}</th>@endif
                @if($placement === 'inline_column')<th style="text-align: left; padding: 2px; font-weight: bold;">{{ trans('ip.description') }}</th>@endif
                @if($config['show_quantity'] ?? true)<th style="text-align: center; padding: 2px; font-weight: bold;">{{ trans('ip.quantity') }}</th>@endif
                @if($config['show_unit_price'] ?? true)<th style="text-align: right; padding: 2px; font-weight: bold;">{{ trans('ip.unit_price') }}</th>@endif
                @if($config['show_tax'] ?? true)<th style="text-align: right; padding: 2px; font-weight: bold;">{{ trans('ip.tax') }}</th>@endif
                @if($config['show_discount'] ?? false)<th style="text-align: right; padding: 2px; font-weight: bold;">{{ trans('ip.discount') }}</th>@endif
                @if($config['show_total'] ?? true)<th style="text-align: right; padding: 2px; font-weight: bold;">{{ trans('ip.total') }}</th>@endif
            </tr>
            @if($placement === 'below_row')
                <tr>
                    <td colspan="{{ $columnCount }}" style="font-size: {{ max(7, ($config['font_size'] ?? 9) - 1) }}pt; color: #4b5563; padding: 2px 4px 4px 8px; font-style: italic;">
                        {{ trans('ip.description') }}
                    </td>
                </tr>
            @endif
        </table>
    </div>
