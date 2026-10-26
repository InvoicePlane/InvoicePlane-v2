@props([
    'config' => [],
    'data' => []
])

<div class="footer-notes" style="font-size: {{ $config['font_size'] ?? 8 }}pt;">
    @if(!empty($config['footer_content']))
        <div style="border-top: 1px solid #e5e7eb; padding-top: 10px; margin-top: 20px;">
            {{ $config['footer_content'] }}
        </div>
    @elseif(!empty($data['footer']))
        <div style="border-top: 1px solid #e5e7eb; padding-top: 10px; margin-top: 20px;">
            {{ $data['footer'] }}
        </div>
    @endif
</div>
