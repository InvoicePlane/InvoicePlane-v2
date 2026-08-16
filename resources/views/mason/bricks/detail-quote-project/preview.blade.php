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

<div style="float: left; padding: 8px; box-sizing: border-box; width: {{ match($config['_width'] ?? 'full') { 'one_third' => '33.33%', 'half' => '50%', 'two_thirds' => '66.66%', default => '100%' } }};">
    <div style="border: 2px dashed #9ca3af; padding: 12px; border-radius: 6px; background-color: #ff0000 !important; min-height: 120px; height: 100%;">
    <table class="w-full text-sm" style="font-size: {{ $config['font_size'] ?? 9 }}pt;">
        <thead class="bg-gray-100">
            <tr>
                @if($config['show_project_name'] ?? true)
                    <th class="text-left p-2 border-b">{{ trans('ip.project') }}</th>
                @endif
                @if($config['show_task_name'] ?? true)
                    <th class="text-left p-2 border-b">{{ trans('ip.task') }}</th>
                @endif
                @if($config['show_description'] ?? true)
                    <th class="text-left p-2 border-b">{{ trans('ip.description') }}</th>
                @endif
                @if($config['show_hours'] ?? true)
                    <th class="text-center p-2 border-b">{{ trans('ip.hours') }}</th>
                @endif
                @if($config['show_rate'] ?? true)
                    <th class="text-right p-2 border-b">{{ trans('ip.rate') }}</th>
                @endif
                @if($config['show_total'] ?? true)
                    <th class="text-right p-2 border-b">{{ trans('ip.total') }}</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @for($i = 1; $i <= 3; $i++)
                <tr class="{{ ($config['alternating_rows'] ?? true) && $i % 2 == 0 ? 'bg-gray-50' : '' }}">
                    @if($config['show_project_name'] ?? true)
                        <td class="p-2">{{ trans('ip.project') }} {{ $i }}</td>
                    @endif
                    @if($config['show_task_name'] ?? true)
                        <td class="p-2">{{ trans('ip.task') }} {{ $i }}</td>
                    @endif
                    @if($config['show_description'] ?? true)
                        <td class="p-2">{{ trans('ip.task_description') }}</td>
                    @endif
                    @if($config['show_hours'] ?? true)
                        <td class="text-center p-2">{{ $i * 5 }}</td>
                    @endif
                    @if($config['show_rate'] ?? true)
                        <td class="text-right p-2">$75.00</td>
                    @endif
                    @if($config['show_total'] ?? true)
                        <td class="text-right p-2">${{ $i * 5 * 75 }}.00</td>
                    @endif
                </tr>
            @endfor
        </tbody>
    </table>

</div>
</div>
