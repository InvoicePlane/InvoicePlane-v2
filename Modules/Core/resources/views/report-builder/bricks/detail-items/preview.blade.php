@props([
    'config' => []
])

    <div style="display: block; width: 100%; min-height: 100px; border: 1px solid #999; padding: 12px; border-radius: 4px; background-color: #CCCCCC; font-size: 10px; color: #333; box-sizing: border-box;">
        <strong>{{ trans('ip.line_items') }}</strong>
        <table style="width: 100%; margin-top: 6px; font-size: 9px;">
            <tr style="border-bottom: 1px solid #999;">
                @if($config['show_description'] ?? true)<td><strong>{{ trans('ip.description') }}</strong></td>@endif
                @if($config['show_quantity'] ?? true)<td style="text-align: center;"><strong>{{ trans('ip.qty') }}</strong></td>@endif
                @if($config['show_price'] ?? true)<td style="text-align: right;"><strong>{{ trans('ip.price') }}</strong></td>@endif
            </tr>
        </table>
    </div>
