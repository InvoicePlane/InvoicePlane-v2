@props([
    'config' => [],
    'data' => []
])

<div class="expense-items" style="font-size: {{ $config['font_size'] ?? 9 }}pt;">
    <table width="100%" cellpadding="4" cellspacing="0" border="1" style="border-collapse: collapse;">
        <thead>
            <tr style="background-color: #f3f4f6;">
                @if($config['show_expense_number'] ?? true)
                    <th align="left" width="12%">{{ trans('ip.expense_number') }}</th>
                @endif
                @if($config['show_expense_date'] ?? true)
                    <th align="left" width="12%">{{ trans('ip.date') }}</th>
                @endif
                @if($config['show_category'] ?? true)
                    <th align="left" width="15%">{{ trans('ip.category') }}</th>
                @endif
                @if($config['show_vendor'] ?? false)
                    <th align="left" width="15%">{{ trans('ip.vendor') }}</th>
                @endif
                @if($config['show_description'] ?? true)
                    <th align="left">{{ trans('ip.description') }}</th>
                @endif
                @if($config['show_amount'] ?? true)
                    <th align="right" width="12%">{{ trans('ip.amount') }}</th>
                @endif
                @if($config['show_status'] ?? true)
                    <th align="center" width="10%">{{ trans('ip.status') }}</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach(($data['expense_items'] ?? []) as $index => $item)
                <tr style="{{ ($config['alternating_rows'] ?? true) && $index % 2 == 1 ? 'background-color: #f9fafb;' : '' }}">
                    @if($config['show_expense_number'] ?? true)
                        <td>{{ $item['expense_number'] ?? '' }}</td>
                    @endif
                    @if($config['show_expense_date'] ?? true)
                        <td>{{ $item['expense_date'] ?? '' }}</td>
                    @endif
                    @if($config['show_category'] ?? true)
                        <td>{{ $item['category'] ?? '' }}</td>
                    @endif
                    @if($config['show_vendor'] ?? false)
                        <td>{{ $item['vendor'] ?? '' }}</td>
                    @endif
                    @if($config['show_description'] ?? true)
                        <td>{{ $item['description'] ?? '' }}</td>
                    @endif
                    @if($config['show_amount'] ?? true)
                        <td align="right">{{ $item['amount'] ?? '0.00' }}</td>
                    @endif
                    @if($config['show_status'] ?? true)
                        <td align="center">{{ $item['status'] ?? '' }}</td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
