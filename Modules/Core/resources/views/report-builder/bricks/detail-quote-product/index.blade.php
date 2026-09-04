@props([
    'config' => [],
    'data' => [],
])

@include('core::report-builder.bricks._shared.product-detail-index', [
    'config' => $config,
    'data' => $data,
    'itemsClass' => 'quote-product-items',
    'dataKey' => 'quote_items',
])
