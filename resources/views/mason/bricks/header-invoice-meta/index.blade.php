@props([
    'config' => [],
    'data' => []
])

<div class="invoice-metadata" style="font-size: {{ $config['font_size'] ?? 10 }}pt; text-align: {{ $config['text_align'] ?? 'right' }};">
    <table width="100%" cellpadding="3" cellspacing="0">
        @if($config['show_invoice_number'] ?? true)
            <tr>
                <td align="right"><strong>{{ trans('ip.invoice_number') }}:</strong></td>
                <td align="right">{{ $data['invoice']['number'] ?? '' }}</td>
            </tr>
        @endif
        @if($config['show_invoice_date'] ?? true)
            <tr>
                <td align="right"><strong>{{ trans('ip.invoice_date') }}:</strong></td>
                <td align="right">{{ $data['invoice']['date'] ?? '' }}</td>
            </tr>
        @endif
        @if($config['show_due_date'] ?? true)
            <tr>
                <td align="right"><strong>{{ trans('ip.due_date') }}:</strong></td>
                <td align="right">{{ $data['invoice']['due_date'] ?? '' }}</td>
            </tr>
        @endif
        @if(($config['show_po_number'] ?? false) && !empty($data['invoice']['po_number']))
            <tr>
                <td align="right"><strong>{{ trans('ip.po_number') }}:</strong></td>
                <td align="right">{{ $data['invoice']['po_number'] }}</td>
            </tr>
        @endif
    </table>
</div>
