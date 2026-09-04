@props([
    'config' => [],
    'data' => [],
])

@include('core::report-builder.bricks._shared.product-detail-index', [
    'config' => $config,
    'data' => $data,
    'itemsClass' => 'invoice-product-items',
    'dataKey' => 'invoice_items',
])
