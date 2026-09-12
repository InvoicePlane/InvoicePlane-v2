@props([
    'config' => []
])

<div style="display: block; width: 100%; min-height: 100px; border: 1px solid #999; padding: 12px; border-radius: 4px; background-color: #CCCCCC; font-size: 10px; color: #333; box-sizing: border-box;">
    <table style="width: 100%; margin-top: 2px; font-size: {{ $config['font_size'] ?? 9 }}pt;">
        <tr style="border-bottom: 1px solid #999;">
            @if($config['show_invoice_number'] ?? true)<th style="text-align: left; padding: 2px; font-weight: bold;">{{ trans('ip.invoice') }}</th>@endif
            @if($config['show_invoice_date'] ?? true)<th style="text-align: left; padding: 2px; font-weight: bold;">{{ trans('ip.date') }}</th>@endif
            @if($config['show_due_date'] ?? true)<th style="text-align: left; padding: 2px; font-weight: bold;">{{ trans('ip.due_date') }}</th>@endif
            @if($config['show_current'] ?? true)<th style="text-align: right; padding: 2px; font-weight: bold;">{{ trans('ip.current') }}</th>@endif
            @if($config['show_30_days'] ?? true)<th style="text-align: right; padding: 2px; font-weight: bold;">{{ trans('ip.days_30') }}</th>@endif
            @if($config['show_60_days'] ?? true)<th style="text-align: right; padding: 2px; font-weight: bold;">{{ trans('ip.days_60') }}</th>@endif
            @if($config['show_90_days'] ?? true)<th style="text-align: right; padding: 2px; font-weight: bold;">{{ trans('ip.days_90') }}</th>@endif
            @if($config['show_over_90_days'] ?? true)<th style="text-align: right; padding: 2px; font-weight: bold;">{{ trans('ip.over_90') }}</th>@endif
            @if($config['show_total_due'] ?? true)<th style="text-align: right; padding: 2px; font-weight: bold;">{{ trans('ip.total_due') }}</th>@endif
        </tr>
    </table>
</div>
