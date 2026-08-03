{{-- Invoice document markup — used by the PDF driver and the on-screen preview. --}}
@php
    $primaryColor = $branding['primary_color'];
    $accentColor  = $branding['accent_color'];
@endphp
<div class="ip-invoice" style="font-family: {{ $branding['font_family'] }}; color: {{ $primaryColor }}; font-size: {{ $branding['font_size'] }}px; line-height: 1.5;">
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 24px;">
        <tr>
            <td style="vertical-align: top;">
                @if ($branding['logo_path'])
                    <img src="{{ $branding['logo_path'] }}" alt="{{ $invoice->company?->name }}" style="max-height: 60px; max-width: 240px; margin-bottom: 8px;">
                @endif
                <div style="font-size: 20px; font-weight: bold;">{{ $invoice->company?->name }}</div>
            </td>
            <td style="vertical-align: top; text-align: right;">
                <div style="font-size: 18px; font-weight: bold; text-transform: uppercase;">{{ trans('ip.invoice') }}</div>
                <div>{{ $invoice->invoice_number ?? trans('ip.draft') }}</div>
            </td>
        </tr>
    </table>

    <table style="width: 100%; border-collapse: collapse; margin-bottom: 24px;">
        <tr>
            <td style="vertical-align: top;">
                <div style="font-weight: bold; margin-bottom: 4px;">{{ trans('ip.bill_to') }}</div>
                <div>{{ $invoice->customer?->company_name }}</div>
                @if ($invoice->customer?->vat_number)
                    <div>{{ trans('ip.vat_id_short') }}: {{ $invoice->customer->vat_number }}</div>
                @endif
            </td>
            <td style="vertical-align: top; text-align: right;">
                <div>{{ trans('ip.invoice_date') }}: {{ $invoice->invoiced_at?->format('Y-m-d') }}</div>
                <div>{{ trans('ip.invoice_due_at') }}: {{ $invoice->invoice_due_at?->format('Y-m-d') }}</div>
            </td>
        </tr>
    </table>

    <table style="width: 100%; border-collapse: collapse; margin-bottom: 24px;">
        <thead>
            <tr>
                <th style="text-align: left; border-bottom: 2px solid {{ $primaryColor }}; padding: 6px 4px;">{{ trans('ip.item') }}</th>
                <th style="text-align: right; border-bottom: 2px solid {{ $primaryColor }}; padding: 6px 4px;">{{ trans('ip.quantity') }}</th>
                <th style="text-align: right; border-bottom: 2px solid {{ $primaryColor }}; padding: 6px 4px;">{{ trans('ip.price') }}</th>
                <th style="text-align: right; border-bottom: 2px solid {{ $primaryColor }}; padding: 6px 4px;">{{ trans('ip.discount') }}</th>
                <th style="text-align: right; border-bottom: 2px solid {{ $primaryColor }}; padding: 6px 4px;">{{ trans('ip.subtotal') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice->invoiceItems as $item)
                <tr>
                    <td style="border-bottom: 1px solid #e5e7eb; padding: 6px 4px;">
                        {{ $item->item_name }}
                        @if ($item->description)
                            <div style="color: {{ $accentColor }};">{{ $item->description }}</div>
                        @endif
                    </td>
                    <td style="text-align: right; border-bottom: 1px solid #e5e7eb; padding: 6px 4px;">{{ $item->quantity + 0 }}</td>
                    <td style="text-align: right; border-bottom: 1px solid #e5e7eb; padding: 6px 4px;">{{ number_format((float) $item->price, 2) }}</td>
                    <td style="text-align: right; border-bottom: 1px solid #e5e7eb; padding: 6px 4px;">{{ number_format((float) $item->discount, 2) }}</td>
                    <td style="text-align: right; border-bottom: 1px solid #e5e7eb; padding: 6px 4px;">{{ number_format((float) $item->subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table style="width: 40%; margin-left: 60%; border-collapse: collapse; margin-bottom: 24px;">
        <tr>
            <td style="padding: 4px;">{{ trans('ip.subtotal') }}</td>
            <td style="text-align: right; padding: 4px;">{{ number_format((float) $invoice->invoice_item_subtotal, 2) }}</td>
        </tr>
        <tr>
            <td style="padding: 4px;">{{ trans('ip.tax') }}</td>
            <td style="text-align: right; padding: 4px;">{{ number_format((float) $invoice->invoice_tax_total + (float) $invoice->item_tax_total, 2) }}</td>
        </tr>
        @if ((float) $invoice->invoice_discount_amount > 0)
            <tr>
                <td style="padding: 4px;">{{ trans('ip.discount') }}</td>
                <td style="text-align: right; padding: 4px;">-{{ number_format((float) $invoice->invoice_discount_amount, 2) }}</td>
            </tr>
        @endif
        <tr>
            <td style="padding: 4px; border-top: 2px solid {{ $primaryColor }}; font-weight: bold;">{{ trans('ip.total') }}</td>
            <td style="text-align: right; padding: 4px; border-top: 2px solid {{ $primaryColor }}; font-weight: bold;">{{ number_format((float) $invoice->invoice_total, 2) }}</td>
        </tr>
    </table>

    @if ($invoice->summary)
        <div style="margin-bottom: 12px;">
            <div style="font-weight: bold;">{{ trans('ip.summary') }}</div>
            <div>{{ $invoice->summary }}</div>
        </div>
    @endif

    @if ($invoice->terms)
        <div style="margin-bottom: 12px;">
            <div style="font-weight: bold;">{{ trans('ip.terms') }}</div>
            <div>{{ $invoice->terms }}</div>
        </div>
    @endif

    @if ($invoice->footer)
        <div style="color: {{ $accentColor }}; margin-top: 24px;">{{ $invoice->footer }}</div>
    @endif
</div>
