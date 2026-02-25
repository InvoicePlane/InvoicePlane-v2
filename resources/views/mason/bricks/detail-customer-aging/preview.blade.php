@props([
    'config' => []
])

<div class="border-2 border-dashed border-gray-300 p-4 rounded bg-white">
    <table class="w-full text-sm" style="font-size: {{ $config['font_size'] ?? 9 }}pt;">
        <thead class="bg-gray-100">
            <tr>
                @if($config['show_invoice_number'] ?? true)
                    <th class="text-left p-2 border-b">{{ trans('ip.invoice') }}</th>
                @endif
                @if($config['show_invoice_date'] ?? true)
                    <th class="text-left p-2 border-b">{{ trans('ip.date') }}</th>
                @endif
                @if($config['show_due_date'] ?? true)
                    <th class="text-left p-2 border-b">{{ trans('ip.due_date') }}</th>
                @endif
                @if($config['show_current'] ?? true)
                    <th class="text-right p-2 border-b">{{ trans('ip.current') }}</th>
                @endif
                @if($config['show_30_days'] ?? true)
                    <th class="text-right p-2 border-b">{{ trans('ip.days_30') }}</th>
                @endif
                @if($config['show_60_days'] ?? true)
                    <th class="text-right p-2 border-b">{{ trans('ip.days_60') }}</th>
                @endif
                @if($config['show_90_days'] ?? true)
                    <th class="text-right p-2 border-b">{{ trans('ip.days_90') }}</th>
                @endif
                @if($config['show_over_90_days'] ?? true)
                    <th class="text-right p-2 border-b">{{ trans('ip.over_90') }}</th>
                @endif
                @if($config['show_total_due'] ?? true)
                    <th class="text-right p-2 border-b">{{ trans('ip.total_due') }}</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @for($i = 1; $i <= 3; $i++)
                <tr class="{{ ($config['alternating_rows'] ?? true) && $i % 2 == 0 ? 'bg-gray-50' : '' }} {{ ($config['highlight_overdue'] ?? true) && $i > 1 ? 'text-red-600' : '' }}">
                    @if($config['show_invoice_number'] ?? true)
                        <td class="p-2">INV-{{ str_pad($i, 4, '0', STR_PAD_LEFT) }}</td>
                    @endif
                    @if($config['show_invoice_date'] ?? true)
                        <td class="p-2">{{ now()->subDays($i * 30)->format('Y-m-d') }}</td>
                    @endif
                    @if($config['show_due_date'] ?? true)
                        <td class="p-2">{{ now()->subDays(($i * 30) - 30)->format('Y-m-d') }}</td>
                    @endif
                    @if($config['show_current'] ?? true)
                        <td class="text-right p-2">{{ $i == 1 ? '$1,500.00' : '-' }}</td>
                    @endif
                    @if($config['show_30_days'] ?? true)
                        <td class="text-right p-2">{{ $i == 2 ? '$2,300.00' : '-' }}</td>
                    @endif
                    @if($config['show_60_days'] ?? true)
                        <td class="text-right p-2">{{ $i == 3 ? '$800.00' : '-' }}</td>
                    @endif
                    @if($config['show_90_days'] ?? true)
                        <td class="text-right p-2">-</td>
                    @endif
                    @if($config['show_over_90_days'] ?? true)
                        <td class="text-right p-2">-</td>
                    @endif
                    @if($config['show_total_due'] ?? true)
                        <td class="text-right p-2 font-bold">{{ $i == 1 ? '$1,500.00' : ($i == 2 ? '$2,300.00' : '$800.00') }}</td>
                    @endif
                </tr>
            @endfor
        </tbody>
        <tfoot class="bg-gray-200 font-bold">
            <tr>
                <td colspan="{{ $config['show_invoice_number'] && $config['show_invoice_date'] && $config['show_due_date'] ? '3' : '1' }}" class="p-2">{{ trans('ip.total') }}</td>
                @if($config['show_current'] ?? true)
                    <td class="text-right p-2">$1,500.00</td>
                @endif
                @if($config['show_30_days'] ?? true)
                    <td class="text-right p-2">$2,300.00</td>
                @endif
                @if($config['show_60_days'] ?? true)
                    <td class="text-right p-2">$800.00</td>
                @endif
                @if($config['show_90_days'] ?? true)
                    <td class="text-right p-2">$0.00</td>
                @endif
                @if($config['show_over_90_days'] ?? true)
                    <td class="text-right p-2">$0.00</td>
                @endif
                @if($config['show_total_due'] ?? true)
                    <td class="text-right p-2">$4,600.00</td>
                @endif
            </tr>
        </tfoot>
    </table>
</div>
