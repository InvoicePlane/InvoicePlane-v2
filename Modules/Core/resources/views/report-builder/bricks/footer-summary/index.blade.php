<div style="font-size: {{ $config['font_size'] ?? 9 }}pt;">
    @if(!empty($config['summary_content']))
        {!! \Stevebauman\Purify\Facades\Purify::config('report')->clean($config['summary_content']) !!}
    @elseif(!empty($data['summary']))
        {{ $data['summary'] }}
    @endif
</div>
