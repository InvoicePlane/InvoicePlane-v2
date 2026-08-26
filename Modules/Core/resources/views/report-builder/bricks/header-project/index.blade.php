<div style="font-size: {{ $config['font_size'] ?? 10 }}pt; text-align: {{ $config['text_align'] ?? 'left' }};">
    @if($config['show_project_number'] ?? true)
        <div><strong>{{ trans('ip.project_number') }}:</strong> {{ $data['project']['project_number'] ?? '' }}</div>
    @endif
    @if($config['show_project_name'] ?? true)
        <div><strong>{{ trans('ip.project_name') }}:</strong> {{ $data['project']['project_name'] ?? '' }}</div>
    @endif
    @if($config['show_start_date'] ?? true)
        <div><strong>{{ trans('ip.start_date') }}:</strong> {{ $data['project']['start_at'] ?? '' }}</div>
    @endif
    @if($config['show_end_date'] ?? true)
        <div><strong>{{ trans('ip.end_date') }}:</strong> {{ $data['project']['end_at'] ?? '' }}</div>
    @endif
    @if($config['show_status'] ?? true)
        <div><strong>{{ trans('ip.status') }}:</strong> {{ $data['project']['project_status'] ?? '' }}</div>
    @endif
</div>
