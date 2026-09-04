@props([
    'config' => [],
    'data' => []
])

<div class="invoice-project-items" style="font-size: {{ $config['font_size'] ?? 9 }}pt;">
    <table width="100%" cellpadding="4" cellspacing="0" border="1" style="border-collapse: collapse;">
        <thead>
            <tr style="background-color: #f3f4f6;">
                @if($config['show_project_name'] ?? true)
                    <th align="left" width="20%">{{ trans('ip.project') }}</th>
                @endif
                @if($config['show_task_name'] ?? true)
                    <th align="left" width="20%">{{ trans('ip.task') }}</th>
                @endif
                @if($config['show_description'] ?? true)
                    <th align="left">{{ trans('ip.description') }}</th>
                @endif
                @if($config['show_hours'] ?? true)
                    <th align="center" width="10%">{{ trans('ip.hours') }}</th>
                @endif
                @if($config['show_rate'] ?? true)
                    <th align="right" width="12%">{{ trans('ip.rate') }}</th>
                @endif
                @if($config['show_total'] ?? true)
                    <th align="right" width="12%">{{ trans('ip.total') }}</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @php
                $currentProject = null;
                $items = $data['project_items'] ?? [];
            @endphp
            @foreach($items as $index => $item)
                @if(($config['group_by_project'] ?? true) && $currentProject !== ($item['project_name'] ?? ''))
                    @php $currentProject = $item['project_name'] ?? ''; @endphp
                    <tr style="background-color: #e5e7eb; font-weight: bold;">
                        <td colspan="6">{{ $currentProject }}</td>
                    </tr>
                @endif
                <tr style="{{ ($config['alternating_rows'] ?? true) && $index % 2 == 1 ? 'background-color: #f9fafb;' : '' }}">
                    @if($config['show_project_name'] ?? true)
                        <td>{{ $item['project_name'] ?? '' }}</td>
                    @endif
                    @if($config['show_task_name'] ?? true)
                        <td>{{ $item['task_name'] ?? '' }}</td>
                    @endif
                    @if($config['show_description'] ?? true)
                        <td>{{ $item['description'] ?? '' }}</td>
                    @endif
                    @if($config['show_hours'] ?? true)
                        <td align="center">{{ $item['hours'] ?? 0 }}</td>
                    @endif
                    @if($config['show_rate'] ?? true)
                        <td align="right">{{ $item['rate'] ?? '0.00' }}</td>
                    @endif
                    @if($config['show_total'] ?? true)
                        <td align="right">{{ $item['total'] ?? '0.00' }}</td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
