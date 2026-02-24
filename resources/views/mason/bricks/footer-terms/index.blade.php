<div style="font-size: {{ $config['font_size'] ?? 8 }}pt;">
    @if(!empty($config['terms_content']))
        {!! $config['terms_content'] !!}
    @elseif(!empty($data['terms']))
        {!! $data['terms'] !!}
    @endif
</div>
