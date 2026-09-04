{{-- Shared print/render markup for the footer text bricks (FooterNotesBrick,
     FooterTermsBrick). Both fall back from the brick's own rich-text config
     to the document's own text field when nothing is configured — only the
     config/data keys differ, passed in as props. --}}
@props([
    'config' => [],
    'data' => [],
    'contentField' => 'footer_content',
    'dataKey' => 'footer',
    'wrapped' => false,
    'outerClass' => null,
])

<div class="{{ $outerClass }}" style="font-size: {{ $config['font_size'] ?? 8 }}pt;">
    @if(!empty($config[$contentField]))
        @if($wrapped)
            <div style="border-top: 1px solid #e5e7eb; padding-top: 10px; margin-top: 20px;">
                {!! $config[$contentField] !!}
            </div>
        @else
            {!! $config[$contentField] !!}
        @endif
    @elseif(!empty($data[$dataKey]))
        @if($wrapped)
            <div style="border-top: 1px solid #e5e7eb; padding-top: 10px; margin-top: 20px;">
                {!! $data[$dataKey] !!}
            </div>
        @else
            {!! $data[$dataKey] !!}
        @endif
    @endif
</div>
