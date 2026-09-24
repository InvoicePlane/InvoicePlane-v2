@props([
    'config' => [],
    'data' => []
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
    if ($config['show_tax'] ?? true) {
        $columnCount++;
    }
    if ($config['show_total'] ?? true) {
        $columnCount++;
    }
    $columnCount = max(1, $columnCount);
@endphp

<div class="line-items" style="font-size: {{ $config['font_size'] ?? 9 }}pt;">
    <table width="100%" cellpadding="4" cellspacing="0" border="1" style="border-collapse: collapse;">
@if($config['show_table_header'] ?? true)
        <thead>
            <tr style="background-color: #f3f4f6;">
                @if($placement === 'inline_column')
                    <th align="left">{{ trans('ip.description') }}</th>
                @endif
                @if($config['show_quantity'] ?? true)
                    <th align="center" width="10%">{{ trans('ip.quantity') }}</th>
                @endif
                @if($config['show_price'] ?? true)
                    <th align="right" width="15%">{{ trans('ip.price') }}</th>
                @endif
                @if($config['show_tax'] ?? true)
                    <th align="right" width="10%">{{ trans('ip.tax') }}</th>
                @endif
                @if($config['show_total'] ?? true)
                    <th align="right" width="15%">{{ trans('ip.total') }}</th>
                @endif
            </tr>
        </thead>
@endif
        <tbody>
            @foreach(($data['items'] ?? []) as $index => $item)
                <tr style="{{ ($config['alternating_rows'] ?? true) && $index % 2 == 1 ? 'background-color: #f9fafb;' : '' }}">
                    @if($placement === 'inline_column')
                        <td>{{ $item['description'] ?? '' }}</td>
                    @endif
                    @if($config['show_quantity'] ?? true)
                        <td align="center">{{ $item['quantity'] ?? 0 }}</td>
                    @endif
                    @if($config['show_price'] ?? true)
                        <td align="right">{{ $item['price'] ?? '0.00' }}</td>
                    @endif
                    @if($config['show_tax'] ?? true)
                        <td align="right">{{ $item['tax'] ?? '0.00' }}</td>
                    @endif
                    @if($config['show_total'] ?? true)
                        <td align="right">{{ $item['total'] ?? '0.00' }}</td>
                    @endif
                </tr>
                @if($placement === 'below_row')
                    <tr style="{{ ($config['alternating_rows'] ?? true) && $index % 2 == 1 ? 'background-color: #f9fafb;' : '' }}">
                        <td colspan="{{ $columnCount }}" style="font-size: {{ max(7, ($config['font_size'] ?? 9) - 1) }}pt; color: #4b5563; padding: 2px 4px 4px 12px;">
                            {{ $item['description'] ?? '' }}
                        </td>
                    </tr>
                @endif
            @endforeach
            @if($data['items_truncated'] ?? false)
                <tr><td colspan="99" style="font-size: 8pt; color: #6b7280; text-align: center; padding: 4px;">{{ trans('ip.report_rows_truncated') }}</td></tr>
            @endif
        </tbody>
    </table>
</div>
