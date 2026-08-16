@props([
    'config' => []
])

@php
$width = match($config['_width'] ?? 'full') {
    'one_third' => 'w-1/3',
    'half' => 'w-1/2',
    'two_thirds' => 'w-2/3',
    default => 'w-full',
};
@endphp

<div class="{{ $width }}" style="float: left; padding: 8px; box-sizing: border-box;">
    <div style="border: 2px dashed #9ca3af; padding: 12px; border-radius: 6px; background-color: #f3f4f6; min-height: 120px; height: 100%;">
    <table class="w-full text-sm" style="font-size: {{ $config['font_size'] ?? 9 }}pt;">
        <thead class="bg-gray-100">
            <tr>
                @if($config['show_expense_number'] ?? true)
                    <th class="text-left p-2 border-b">{{ trans('ip.expense_number') }}</th>
                @endif
                @if($config['show_expense_date'] ?? true)
                    <th class="text-left p-2 border-b">{{ trans('ip.date') }}</th>
                @endif
                @if($config['show_category'] ?? true)
                    <th class="text-left p-2 border-b">{{ trans('ip.category') }}</th>
                @endif
                @if($config['show_vendor'] ?? false)
                    <th class="text-left p-2 border-b">{{ trans('ip.vendor') }}</th>
                @endif
                @if($config['show_description'] ?? true)
                    <th class="text-left p-2 border-b">{{ trans('ip.description') }}</th>
                @endif
                @if($config['show_amount'] ?? true)
                    <th class="text-right p-2 border-b">{{ trans('ip.amount') }}</th>
                @endif
                @if($config['show_status'] ?? true)
                    <th class="text-center p-2 border-b">{{ trans('ip.status') }}</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @for($i = 1; $i <= 3; $i++)
                <tr class="{{ ($config['alternating_rows'] ?? true) && $i % 2 == 0 ? 'bg-gray-50' : '' }}">
                    @if($config['show_expense_number'] ?? true)
                        <td class="p-2">EXP-{{ str_pad($i, 4, '0', STR_PAD_LEFT) }}</td>
                    @endif
                    @if($config['show_expense_date'] ?? true)
                        <td class="p-2">{{ now()->subDays($i * 5)->format('Y-m-d') }}</td>
                    @endif
                    @if($config['show_category'] ?? true)
                        <td class="p-2">{{ trans('ip.category') }} {{ $i }}</td>
                    @endif
                    @if($config['show_vendor'] ?? false)
                        <td class="p-2">{{ trans('ip.vendor') }} {{ $i }}</td>
                    @endif
                    @if($config['show_description'] ?? true)
                        <td class="p-2">{{ trans('ip.expense_description') }}</td>
                    @endif
                    @if($config['show_amount'] ?? true)
                        <td class="text-right p-2">${{ $i * 250 }}.00</td>
                    @endif
                    @if($config['show_status'] ?? true)
                        <td class="text-center p-2">{{ $i % 2 == 0 ? trans('ip.paid') : trans('ip.pending') }}</td>
                    @endif
                </tr>
            @endfor
        </tbody>
    </table>

</div>
</div>
