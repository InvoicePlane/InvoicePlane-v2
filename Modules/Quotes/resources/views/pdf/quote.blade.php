{{-- Quote document markup — used by the PDF driver and the on-screen preview. --}}
@php
    $primaryColor = $branding['primary_color'];
    $accentColor  = $branding['accent_color'];
@endphp
<div class="ip-quote" style="font-family: {{ $branding['font_family'] }}; color: {{ $primaryColor }}; font-size: {{ $branding['font_size'] }}px; line-height: 1.5;">
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 24px;">
        <tr>
            <td style="vertical-align: top;">
                @if ($branding['logo_path'])
                    <img src="{{ $branding['logo_path'] }}" alt="{{ $quote->company?->name }}" style="max-height: 60px; max-width: 240px; margin-bottom: 8px;">
                @endif
                <div style="font-size: 20px; font-weight: bold;">{{ $quote->company?->name }}</div>
                @if ($quote->company?->vat_number)
                    <div>{{ trans('ip.vat_id_short') }}: {{ $quote->company->vat_number }}</div>
                @endif
                @if ($quote->company?->id_number)
                    <div>{{ trans('ip.id_number') }}: {{ $quote->company->id_number }}</div>
                @endif
                @if ($quote->company?->coc_number)
                    <div>{{ trans('ip.coc_number') }}: {{ $quote->company->coc_number }}</div>
                @endif
            </td>
            <td style="vertical-align: top; text-align: right;">
                <div style="font-size: 18px; font-weight: bold; text-transform: uppercase;">{{ trans('ip.quote') }}</div>
                <div>{{ $quote->quote_number ?? trans('ip.draft') }}</div>
            </td>
        </tr>
    </table>

    <table style="width: 100%; border-collapse: collapse; margin-bottom: 24px;">
        <tr>
            <td style="vertical-align: top;">
                <div style="font-weight: bold; margin-bottom: 4px;">{{ trans('ip.bill_to') }}</div>
                <div>{{ $quote->prospect?->company_name }}</div>
                @if ($quote->prospect?->vat_number)
                    <div>{{ trans('ip.vat_id_short') }}: {{ $quote->prospect->vat_number }}</div>
                @endif
            </td>
            <td style="vertical-align: top; text-align: right;">
                <div>{{ trans('ip.quote_date') }}: {{ $quote->quoted_at?->format('Y-m-d') }}</div>
                <div>{{ trans('ip.expires_at') }}: {{ $quote->quote_expires_at?->format('Y-m-d') }}</div>
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
            @foreach ($quote->quoteItems as $item)
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
            <td style="text-align: right; padding: 4px;">{{ number_format((float) $quote->quote_item_subtotal, 2) }}</td>
        </tr>
        <tr>
            <td style="padding: 4px;">{{ trans('ip.tax') }}</td>
            <td style="text-align: right; padding: 4px;">{{ number_format((float) $quote->quote_tax_total + (float) $quote->item_tax_total, 2) }}</td>
        </tr>
        @if ((float) $quote->quote_discount_amount > 0)
            <tr>
                <td style="padding: 4px;">{{ trans('ip.discount') }}</td>
                <td style="text-align: right; padding: 4px;">-{{ number_format((float) $quote->quote_discount_amount, 2) }}</td>
            </tr>
        @endif
        <tr>
            <td style="padding: 4px; border-top: 2px solid {{ $primaryColor }}; font-weight: bold;">{{ trans('ip.total') }}</td>
            <td style="text-align: right; padding: 4px; border-top: 2px solid {{ $primaryColor }}; font-weight: bold;">{{ number_format((float) $quote->quote_total, 2) }}</td>
        </tr>
    </table>

    @if ($quote->summary)
        <div style="margin-bottom: 12px;">
            <div style="font-weight: bold;">{{ trans('ip.summary') }}</div>
            <div>{{ $quote->summary }}</div>
        </div>
    @endif

    @if ($quote->terms)
        <div style="margin-bottom: 12px;">
            <div style="font-weight: bold;">{{ trans('ip.terms') }}</div>
            <div>{{ $quote->terms }}</div>
        </div>
    @endif

    @if ($quote->footer)
        <div style="color: {{ $accentColor }}; margin-top: 24px;">{{ $quote->footer }}</div>
    @endif

    @if (count($signatures) > 0)
        <div style="margin-top: 24px;">
            <div style="font-weight: bold; margin-bottom: 8px;">{{ trans('ip.quote_signatures') }}</div>
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    @foreach ($signatures as $signature)
                        <td style="vertical-align: bottom; padding: 0 12px 0 0; width: {{ (int) (100 / count($signatures)) }}%;">
                            <img src="{{ $signature['path'] }}" alt="{{ $signature['signer_name'] }}" style="max-height: 80px; max-width: 100%; border-bottom: 1px solid {{ $primaryColor }};">
                            <div style="margin-top: 4px;">
                                {{ trans('ip.quote_signed_confirmation', ['name' => $signature['signer_name'], 'date' => $signature['signed_at']?->format('Y-m-d')]) }}
                            </div>
                        </td>
                    @endforeach
                </tr>
            </table>
        </div>
    @endif
</div>
