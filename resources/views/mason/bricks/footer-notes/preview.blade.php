@props([
    'config' => []
])

<div class="border-2 border-dashed border-gray-300 p-4 rounded bg-white">
    <div style="font-size: {{ $config['font_size'] ?? 8 }}pt;" class="text-gray-600">
        @if(!empty($config['notes_content']))
            <div class="prose prose-sm max-w-none">
                {!! $config['notes_content'] !!}
            </div>
        @else
            <p class="text-sm italic">{{ trans('ip.footer_notes_placeholder') }}</p>
        @endif
    </div>
</div>
