@props([
    'config' => []
])

<div class="border-2 border-dashed border-gray-300 p-4 rounded bg-white">
    <div style="text-align: {{ $config['text_align'] ?? 'right' }}; font-size: {{ $config['font_size'] ?? 10 }}pt;">
        <table class="w-full text-sm">
            @if($config['show_invoice_number'] ?? true)
                <tr>
                    <td class="font-semibold pr-2">{{ trans('ip.invoice_number') }}:</td>
                    <td class="text-gray-700">INV-2024-001</td>
                </tr>
            @endif
            @if($config['show_invoice_date'] ?? true)
                <tr>
                    <td class="font-semibold pr-2">{{ trans('ip.invoice_date') }}:</td>
                    <td class="text-gray-700">{{ date('Y-m-d') }}</td>
                </tr>
            @endif
            @if($config['show_due_date'] ?? true)
                <tr>
                    <td class="font-semibold pr-2">{{ trans('ip.due_date') }}:</td>
                    <td class="text-gray-700">{{ date('Y-m-d', strtotime('+30 days')) }}</td>
                </tr>
            @endif
            @if($config['show_po_number'] ?? false)
                <tr>
                    <td class="font-semibold pr-2">{{ trans('ip.po_number') }}:</td>
                    <td class="text-gray-700">PO-12345</td>
                </tr>
            @endif
        </table>
    </div>
</div>
