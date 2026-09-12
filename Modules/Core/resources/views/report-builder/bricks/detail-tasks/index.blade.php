<table style="font-size: {{ $config['font_size'] ?? 9 }}pt; width: 100%; border-collapse: collapse;">
    <thead style="font-weight: {{ $config['header_style'] ?? 'bold' }};">
        <tr style="border-bottom: 2px solid #666;">
            @if($config['show_task_number'] ?? true)
                <th style="text-align: left; padding: 8px 4px;">{{ trans('ip.number') }}</th>
            @endif
            @if($config['show_task_name'] ?? true)
                <th style="text-align: left; padding: 8px 4px;">{{ trans('ip.task_name') }}</th>
            @endif
            @if($config['show_description'] ?? true)
                <th style="text-align: left; padding: 8px 4px;">{{ trans('ip.description') }}</th>
            @endif
            @if($config['show_due_at'] ?? false)
                <th style="text-align: left; padding: 8px 4px;">{{ trans('ip.due_date') }}</th>
            @endif
            @if($config['show_task_price'] ?? true)
                <th style="text-align: right; padding: 8px 4px;">{{ trans('ip.price') }}</th>
            @endif
            @if($config['show_task_status'] ?? true)
                <th style="text-align: center; padding: 8px 4px;">{{ trans('ip.status') }}</th>
            @endif
        </tr>
    </thead>
    <tbody>
        @foreach(($data['tasks'] ?? []) as $task)
            <tr style="border-bottom: 1px solid #ccc;">
                @if($config['show_task_number'] ?? true)
                    <td style="padding: 6px 4px;">{{ $task['task_number'] ?? '' }}</td>
                @endif
                @if($config['show_task_name'] ?? true)
                    <td style="padding: 6px 4px;">{{ $task['task_name'] ?? '' }}</td>
                @endif
                @if($config['show_description'] ?? true)
                    <td style="padding: 6px 4px;">{{ $task['description'] ?? '' }}</td>
                @endif
                @if($config['show_due_at'] ?? false)
                    <td style="padding: 6px 4px;">{{ $task['due_at'] ?? '' }}</td>
                @endif
                @if($config['show_task_price'] ?? true)
                    <td style="padding: 6px 4px; text-align: right;">{{ $task['task_price'] ?? '' }}</td>
                @endif
                @if($config['show_task_status'] ?? true)
                    <td style="padding: 6px 4px; text-align: center;">{{ $task['task_status'] ?? '' }}</td>
                @endif
            </tr>
        @endforeach
    </tbody>
</table>
