@props([
    'config' => []
])

@php
$widthValue = match($config['_width'] ?? 'full') {
    'one_third' => '33.33%',
    'half' => '50%',
    'two_thirds' => '66.66%',
    default => '100%'
};
@endphp

<div style="display: inline-block; vertical-align: top; width: {{ $widthValue }}; padding-right: 8px; box-sizing: border-box;">
    <div style="display: block; width: 100%; min-height: 100px; border: 1px solid #999; padding: 12px; border-radius: 4px; background-color: #CCCCCC; font-size: 10px; color: #333; box-sizing: border-box;">
        <table style="width: 100%; margin-top: 2px; font-size: {{ $config['font_size'] ?? 9 }}pt;">
            <tr style="border-bottom: 1px solid #999;">
                @if($config['show_expense_number'] ?? true)<th style="text-align: left; padding: 2px; font-weight: bold;">{{ trans('ip.expense_number') }}</th>@endif
                @if($config['show_expense_date'] ?? true)<th style="text-align: left; padding: 2px; font-weight: bold;">{{ trans('ip.date') }}</th>@endif
                @if($config['show_category'] ?? true)<th style="text-align: left; padding: 2px; font-weight: bold;">{{ trans('ip.category') }}</th>@endif
                @if($config['show_vendor'] ?? false)<th style="text-align: left; padding: 2px; font-weight: bold;">{{ trans('ip.vendor') }}</th>@endif
                @if($config['show_description'] ?? true)<th style="text-align: left; padding: 2px; font-weight: bold;">{{ trans('ip.description') }}</th>@endif
                @if($config['show_amount'] ?? true)<th style="text-align: right; padding: 2px; font-weight: bold;">{{ trans('ip.amount') }}</th>@endif
                @if($config['show_status'] ?? true)<th style="text-align: center; padding: 2px; font-weight: bold;">{{ trans('ip.status') }}</th>@endif
            </tr>
        </table>
    </div>
</div>
