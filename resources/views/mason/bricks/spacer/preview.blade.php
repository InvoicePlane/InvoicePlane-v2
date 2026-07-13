@props([
    'config' => []
])

<div class="border-2 border-dashed border-gray-300 rounded bg-gray-50 flex items-center justify-center text-xs text-gray-500"
     style="height: {{ min((int) ($config['height'] ?? 20), 100) }}px;">
    {{ trans('ip.spacer') }} — {{ (int) ($config['height'] ?? 20) }}px
</div>
