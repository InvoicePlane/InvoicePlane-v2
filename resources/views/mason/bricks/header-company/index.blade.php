@props([
    'config' => [],
    'data' => []
])

<div class="company-header" style="font-size: {{ $config['font_size'] ?? 10 }}pt; text-align: {{ $config['text_align'] ?? 'left' }};">
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            @if(($config['show_logo'] ?? true) && isset($data['company']['logo_path']))
                <td width="100" valign="top">
                    <img src="{{ $data['company']['logo_path'] }}" alt="{{ trans('ip.logo') }}" style="max-width: 100px; max-height: 80px;">
                </td>
            @endif
            <td valign="top" style="font-weight: {{ $config['font_weight'] ?? 'bold' }};">
                <strong style="font-size: {{ ($config['font_size'] ?? 10) + 2 }}pt;">{{ $data['company']['name'] ?? '' }}</strong><br>
                @if($config['show_address'] ?? true)
                    {{ $data['company']['address'] ?? '' }}<br>
                    {{ $data['company']['city'] ?? '' }} {{ $data['company']['postal_code'] ?? '' }}<br>
                @endif
                @if($config['show_phone'] ?? true)
                    {{ trans('ip.phone') }}: {{ $data['company']['phone'] ?? '' }}<br>
                @endif
                @if($config['show_email'] ?? true)
                    {{ trans('ip.email') }}: {{ $data['company']['email'] ?? '' }}<br>
                @endif
                @if($config['show_vat_id'] ?? true)
                    {{ trans('ip.vat_id') }}: {{ $data['company']['vat_id'] ?? '' }}<br>
                @endif
            </td>
        </tr>
    </table>
</div>
