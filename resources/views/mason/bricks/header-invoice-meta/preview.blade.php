@props([
    'config' => []
])

    <div style="display: block; width: 100%; min-height: 100px; border: 1px solid #999; padding: 12px; border-radius: 4px; background-color: #CCCCCC; font-size: 11px; color: #333; box-sizing: border-box;">
        <strong>{{ trans('ip.invoice_metadata') }}</strong>
        <table style="width: 100%; margin-top: 6px; font-size: 10px;">
            @if($config['show_invoice_number'] ?? true)
                <tr>
                    <td style="padding: 2px; font-weight: bold;">{{ trans('ip.invoice_number') }}:</td>
                    <td style="padding: 2px;">INV-2024-001</td>
                </tr>
            @endif
            @if($config['show_invoice_date'] ?? true)
                <tr>
                    <td style="padding: 2px; font-weight: bold;">{{ trans('ip.invoice_date') }}:</td>
                    <td style="padding: 2px;">{{ date('Y-m-d') }}</td>
                </tr>
            @endif
            @if($config['show_due_date'] ?? true)
                <tr>
                    <td style="padding: 2px; font-weight: bold;">{{ trans('ip.due_date') }}:</td>
                    <td style="padding: 2px;">{{ date('Y-m-d', strtotime('+30 days')) }}</td>
                </tr>
            @endif
            @if($config['show_po_number'] ?? false)
                <tr>
                    <td style="padding: 2px; font-weight: bold;">{{ trans('ip.po_number') }}:</td>
                    <td style="padding: 2px;">PO-12345</td>
                </tr>
            @endif
        </table>
    </div>
