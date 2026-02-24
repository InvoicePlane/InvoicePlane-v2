<div style="font-size: {{ $config['font_size'] ?? 9 }}pt;">
    @if(!empty($config['summary_content']))
        {!! $config['summary_content'] !!}
    @elseif(!empty($data['summary']))
        {!! $data['summary'] !!}
    @endif
</div>
