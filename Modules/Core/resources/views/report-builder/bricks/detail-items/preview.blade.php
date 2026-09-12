@props([
    'config' => []
])

@php
    $placement = $config['description_placement'] ?? (isset($config['show_description']) ? ($config['show_description'] ? 'inline_column' : 'hidden') : 'inline_column');
    $columnCount = 0;
    if ($placement === 'inline_column') {
        $columnCount++;
    }
    if ($config['show_quantity'] ?? true) {
        $columnCount++;
    }
    if ($config['show_price'] ?? true) {
        $columnCount++;
    }
    $columnCount = max(1, $columnCount);
@endphp

    <div style="display: block; width: 100%; min-height: 100px; border: 1px solid #999; padding: 12px; border-radius: 4px; background-color: #CCCCCC; font-size: 10px; color: #333; box-sizing: border-box;">
        <strong>{{ trans('ip.line_items') }}</strong>
        <table style="width: 100%; margin-top: 6px; font-size: 9px;">
            <tr style="border-bottom: 1px solid #999;">
                @if($placement === 'inline_column')<td><strong>{{ trans('ip.description') }}</strong></td>@endif
                @if($config['show_quantity'] ?? true)<td style="text-align: center;"><strong>{{ trans('ip.qty') }}</strong></td>@endif
                @if($config['show_price'] ?? true)<td style="text-align: right;"><strong>{{ trans('ip.price') }}</strong></td>@endif
            </tr>
            @if($placement === 'below_row')
                <tr>
                    <td colspan="{{ $columnCount }}" style="color: #4b5563; padding: 2px 4px 4px 8px; font-style: italic;">
                        {{ trans('ip.description') }}
                    </td>
                </tr>
            @endif
        </table>
    </div>
