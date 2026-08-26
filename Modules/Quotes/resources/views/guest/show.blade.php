<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>{{ trans('ip.quote') }} {{ $quote->quote_number }}</title>
    @vite(['resources/css/guest.css', 'resources/js/signature-pad.js'])
</head>
<body class="bg-gray-50 text-gray-900 min-h-screen p-4">
    <div class="max-w-3xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-lg font-semibold">{{ trans('ip.quote') }} {{ $quote->quote_number }}</h1>
            <a href="{{ route('quotes.guest.pdf', $quote) }}" class="text-sm underline">
                {{ trans('ip.download_pdf') }}
            </a>
        </div>

        @if (session('status'))
            <div class="text-sm text-green-700 bg-green-50 border border-green-200 rounded p-3">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="text-sm text-red-600 bg-red-50 border border-red-200 rounded p-3">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="bg-white rounded-lg shadow p-6 overflow-x-auto">
            {!! $html !!}
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            @if ($signature)
                <p class="text-sm text-gray-700">
                    {{ trans('ip.quote_signed_confirmation', ['name' => $signature->signer_name, 'date' => $signature->signed_at?->format('Y-m-d H:i')]) }}
                </p>
            @else
                <h2 class="text-base font-semibold mb-4">{{ trans('ip.view_and_sign_quote') }}</h2>
                <form method="POST" action="{{ route('quotes.guest.sign', $quote) }}" id="signature-form">
                    @csrf
                    <label for="signer_name" class="block text-sm font-medium mb-1">{{ trans('ip.signer_name') }}</label>
                    <input type="text" name="signer_name" id="signer_name" required
                           class="w-full rounded border border-gray-300 mb-4 px-2 py-1">

                    <fieldset class="mb-3">
                        <legend class="text-sm font-medium mb-1">{{ trans('ip.view_and_sign_quote') }}</legend>
                        <div class="flex gap-4">
                            <label class="text-sm inline-flex items-center gap-1">
                                <input type="radio" name="signature_mode" value="draw" id="signature-mode-draw" checked>
                                {{ trans('ip.draw_signature') }}
                            </label>
                            <label class="text-sm inline-flex items-center gap-1">
                                <input type="radio" name="signature_mode" value="type" id="signature-mode-type">
                                {{ trans('ip.type_signature') }}
                            </label>
                        </div>
                    </fieldset>

                    <div id="signature-draw-panel">
                        <canvas id="signature-pad" class="border border-gray-300 rounded w-full h-50"></canvas>
                        <button type="button" id="signature-clear" class="text-sm underline mt-2">
                            {{ trans('ip.clear_signature') }}
                        </button>
                    </div>

                    <div id="signature-type-panel" class="hidden">
                        <label for="signature-text" class="block text-sm font-medium mb-1">{{ trans('ip.typed_signature_label') }}</label>
                        <input type="text" id="signature-text" class="w-full rounded border border-gray-300 px-2 py-1">
                    </div>

                    <input type="hidden" name="signature_data" id="signature_data">

                    <div class="flex items-center justify-end mt-3">
                        <button type="submit" class="bg-gray-900 text-white rounded px-4 py-2">
                            {{ trans('ip.submit') }}
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</body>
</html>
