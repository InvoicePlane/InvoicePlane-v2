@props([
    'config' => []
])

    <div style="display: block; width: 100%; min-height: 100px; border: 1px solid #999; padding: 12px; border-radius: 4px; background-color: #CCCCCC; font-size: 11px; color: #333; box-sizing: border-box;">
        <strong>{{ trans('ip.project_header') }}</strong>
        @if($config['show_project_number'] ?? true)<br>{{ trans('ip.project_number') }}: {{ 'PROJECT-001' }}@endif
        @if($config['show_project_name'] ?? true)<br>{{ trans('ip.project_name') }}: {{ 'Sample Project' }}@endif
        @if($config['show_start_date'] ?? true)<br>{{ trans('ip.start_date') }}: {{ now()->format('Y-m-d') }}@endif
        @if($config['show_end_date'] ?? true)<br>{{ trans('ip.end_date') }}: {{ now()->addDays(30)->format('Y-m-d') }}@endif
        @if($config['show_status'] ?? true)<br>{{ trans('ip.status') }}: {{ trans('ip.in_progress') }}@endif
    </div>
