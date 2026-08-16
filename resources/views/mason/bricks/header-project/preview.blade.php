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
    <div style="display: block; width: 100%; min-height: 100px; border: 1px solid #999; padding: 12px; border-radius: 4px; background-color: #CCCCCC; font-size: 11px; color: #333; box-sizing: border-box;">
        <strong>{{ trans('ip.project_header') }}</strong>
        @if($config['show_project_number'] ?? true)<br>{{ trans('ip.project_number') }}: PROJECT-001@endif
        @if($config['show_project_name'] ?? true)<br>{{ trans('ip.project_name') }}: Sample Project@endif
        @if($config['show_start_date'] ?? true)<br>{{ trans('ip.start_date') }}: {{ now()->format('Y-m-d') }}@endif
        @if($config['show_end_date'] ?? true)<br>{{ trans('ip.end_date') }}: {{ now()->addDays(30)->format('Y-m-d') }}@endif
        @if($config['show_status'] ?? true)<br>{{ trans('ip.status') }}: {{ trans('ip.in_progress') }}@endif
    </div>
</div>
