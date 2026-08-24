@props([
    'config' => []
])

    <div style="display: block; width: 100%; min-height: 100px; border: 1px solid #999; padding: 12px; border-radius: 4px; background-color: #CCCCCC; font-size: 10px; color: #333; box-sizing: border-box;">
        <table style="width: 100%; margin-top: 2px; font-size: {{ $config['font_size'] ?? 9 }}pt;">
            <tr style="border-bottom: 1px solid #999;">
                @if($config['show_project_name'] ?? true)<th style="text-align: left; padding: 2px; font-weight: bold;">{{ trans('ip.project') }}</th>@endif
                @if($config['show_task_name'] ?? true)<th style="text-align: left; padding: 2px; font-weight: bold;">{{ trans('ip.task') }}</th>@endif
                @if($config['show_description'] ?? true)<th style="text-align: left; padding: 2px; font-weight: bold;">{{ trans('ip.description') }}</th>@endif
                @if($config['show_hours'] ?? true)<th style="text-align: center; padding: 2px; font-weight: bold;">{{ trans('ip.hours') }}</th>@endif
                @if($config['show_rate'] ?? true)<th style="text-align: right; padding: 2px; font-weight: bold;">{{ trans('ip.rate') }}</th>@endif
                @if($config['show_total'] ?? true)<th style="text-align: right; padding: 2px; font-weight: bold;">{{ trans('ip.total') }}</th>@endif
            </tr>
        </table>
    </div>
