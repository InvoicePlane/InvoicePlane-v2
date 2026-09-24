@props([
    'config' => [],
    'data' => [],
])

@include('core::report-builder.bricks._shared.footer-text-index', [
    'config' => $config,
    'data' => $data,
    'contentField' => 'footer_content',
    'dataKey' => 'footer',
    'wrapped' => true,
    'outerClass' => 'footer-notes',
])
