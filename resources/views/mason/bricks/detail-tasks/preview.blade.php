<div class="bg-white p-4 border-2 border-dashed border-gray-300 rounded" style="font-size: {{ $config['font_size'] ?? 9 }}pt;">
    <div class="font-bold text-lg mb-3">{{ trans('ip.tasks_table') }}</div>
    <table class="w-full border-collapse">
        <thead style="font-weight: {{ $config['header_style'] ?? 'bold' }};">
            <tr class="border-b-2 border-gray-400">
                @if($config['show_task_number'] ?? true)
                    <th class="text-left py-2 px-1">{{ trans('ip.number') }}</th>
                @endif
                @if($config['show_task_name'] ?? true)
                    <th class="text-left py-2 px-1">{{ trans('ip.task_name') }}</th>
                @endif
                @if($config['show_description'] ?? true)
                    <th class="text-left py-2 px-1">{{ trans('ip.description') }}</th>
                @endif
                @if($config['show_due_at'] ?? false)
                    <th class="text-left py-2 px-1">{{ trans('ip.due_date') }}</th>
                @endif
                @if($config['show_task_price'] ?? true)
                    <th class="text-right py-2 px-1">{{ trans('ip.price') }}</th>
                @endif
                @if($config['show_task_status'] ?? true)
                    <th class="text-center py-2 px-1">{{ trans('ip.status') }}</th>
                @endif
            </tr>
        </thead>
        <tbody class="text-gray-600">
            <tr class="border-b border-gray-200">
                @if($config['show_task_number'] ?? true)
                    <td class="py-2 px-1">TASK-001</td>
                @endif
                @if($config['show_task_name'] ?? true)
                    <td class="py-2 px-1">Sample Task</td>
                @endif
                @if($config['show_description'] ?? true)
                    <td class="py-2 px-1">Task description</td>
                @endif
                @if($config['show_due_at'] ?? false)
                    <td class="py-2 px-1">{{ now()->addDays(7)->format('Y-m-d') }}</td>
                @endif
                @if($config['show_task_price'] ?? true)
                    <td class="py-2 px-1 text-right">$100.00</td>
                @endif
                @if($config['show_task_status'] ?? true)
                    <td class="py-2 px-1 text-center">{{ trans('ip.pending') }}</td>
                @endif
            </tr>
        </tbody>
    </table>
</div>
