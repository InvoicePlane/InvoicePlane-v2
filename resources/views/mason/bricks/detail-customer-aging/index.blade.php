@props([
    'config' => [],
    'data' => []
])

<div class="customer-aging" style="font-size: {{ $config['font_size'] ?? 9 }}pt;">
    <table width="100%" cellpadding="4" cellspacing="0" border="1" style="border-collapse: collapse;">
        <thead>
            <tr style="background-color: #f3f4f6;">
                @if($config['show_invoice_number'] ?? true)
                    <th align="left">{{ trans('ip.invoice') }}</th>
                @endif
                @if($config['show_invoice_date'] ?? true)
                    <th align="left">{{ trans('ip.date') }}</th>
                @endif
                @if($config['show_due_date'] ?? true)
                    <th align="left">{{ trans('ip.due_date') }}</th>
                @endif
                @if($config['show_current'] ?? true)
                    <th align="right" width="10%">{{ trans('ip.current') }}</th>
                @endif
                @if($config['show_30_days'] ?? true)
                    <th align="right" width="10%">{{ trans('ip.days_30') }}</th>
                @endif
                @if($config['show_60_days'] ?? true)
                    <th align="right" width="10%">{{ trans('ip.days_60') }}</th>
                @endif
                @if($config['show_90_days'] ?? true)
                    <th align="right" width="10%">{{ trans('ip.days_90') }}</th>
                @endif
                @if($config['show_over_90_days'] ?? true)
                    <th align="right" width="10%">{{ trans('ip.over_90') }}</th>
                @endif
                @if($config['show_total_due'] ?? true)
                    <th align="right" width="12%">{{ trans('ip.total_due') }}</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach(($data['aging_items'] ?? []) as $index => $item)
                <tr style="{{ ($config['alternating_rows'] ?? true) && $index % 2 == 1 ? 'background-color: #f9fafb;' : '' }}{{ ($config['highlight_overdue'] ?? true) && ($item['days_overdue'] ?? 0) > 0 ? ' color: #dc2626;' : '' }}">
                    @if($config['show_invoice_number'] ?? true)
                        <td>{{ $item['invoice_number'] ?? '' }}</td>
                    @endif
                    @if($config['show_invoice_date'] ?? true)
                        <td>{{ $item['invoice_date'] ?? '' }}</td>
                    @endif
                    @if($config['show_due_date'] ?? true)
                        <td>{{ $item['due_date'] ?? '' }}</td>
                    @endif
                    @if($config['show_current'] ?? true)
                        <td align="right">{{ $item['current'] ?? '-' }}</td>
                    @endif
                    @if($config['show_30_days'] ?? true)
                        <td align="right">{{ $item['days_30'] ?? '-' }}</td>
                    @endif
                    @if($config['show_60_days'] ?? true)
                        <td align="right">{{ $item['days_60'] ?? '-' }}</td>
                    @endif
                    @if($config['show_90_days'] ?? true)
                        <td align="right">{{ $item['days_90'] ?? '-' }}</td>
                    @endif
                    @if($config['show_over_90_days'] ?? true)
                        <td align="right">{{ $item['over_90'] ?? '-' }}</td>
                    @endif
                    @if($config['show_total_due'] ?? true)
                        <td align="right" style="font-weight: bold;">{{ $item['total_due'] ?? '0.00' }}</td>
                    @endif
                </tr>
            @endforeach
        </tbody>
        @if(!empty($data['aging_totals']))
        <tfoot style="background-color: #e5e7eb; font-weight: bold;">
            <tr>
                <td colspan="{{ ($config['show_invoice_number'] ?? true) + ($config['show_invoice_date'] ?? true) + ($config['show_due_date'] ?? true) }}">{{ trans('ip.total') }}</td>
                @if($config['show_current'] ?? true)
                    <td align="right">{{ $data['aging_totals']['current'] ?? '0.00' }}</td>
                @endif
                @if($config['show_30_days'] ?? true)
                    <td align="right">{{ $data['aging_totals']['days_30'] ?? '0.00' }}</td>
                @endif
                @if($config['show_60_days'] ?? true)
                    <td align="right">{{ $data['aging_totals']['days_60'] ?? '0.00' }}</td>
                @endif
                @if($config['show_90_days'] ?? true)
                    <td align="right">{{ $data['aging_totals']['days_90'] ?? '0.00' }}</td>
                @endif
                @if($config['show_over_90_days'] ?? true)
                    <td align="right">{{ $data['aging_totals']['over_90'] ?? '0.00' }}</td>
                @endif
                @if($config['show_total_due'] ?? true)
                    <td align="right">{{ $data['aging_totals']['total_due'] ?? '0.00' }}</td>
                @endif
            </tr>
        </tfoot>
        @endif
    </table>
</div>
