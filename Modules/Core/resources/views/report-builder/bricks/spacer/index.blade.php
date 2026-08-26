@props([
    'config' => [],
    'data' => []
])

<div class="spacer" style="height: {{ (int) ($config['height'] ?? 20) }}px;"></div>
