@props([
    'config' => [],
    'data' => []
])

<div class="footer-notes" style="font-size: {{ $config['font_size'] ?? 8 }}pt;">
    @if(!empty($config['notes_content']))
        <div style="border-top: 1px solid #e5e7eb; padding-top: 10px; margin-top: 20px;">
            {!! $config['notes_content'] !!}
        </div>
    @elseif(!empty($data['notes']))
        <div style="border-top: 1px solid #e5e7eb; padding-top: 10px; margin-top: 20px;">
            {{ $data['notes'] }}
        </div>
    @endif
</div>
