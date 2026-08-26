<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>{{ trans('ip.quote') }} {{ $quote->quote_number }}</title>
    @vite(['resources/css/guest.css'])
</head>
<body class="bg-gray-50 text-gray-900 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-sm bg-white rounded-lg shadow p-6">
        <h1 class="text-lg font-semibold mb-4">{{ trans('ip.quote') }} {{ $quote->quote_number }}</h1>

        @if ($errors->any())
            <div class="mb-4 text-sm text-red-600">
                {{ $errors->first('password') }}
            </div>
        @endif

        <form method="POST" action="{{ route('quotes.guest.password', $quote) }}">
            @csrf
            <label for="password" class="block text-sm font-medium mb-1">{{ trans('ip.quote_password') }}</label>
            <input type="password" name="password" id="password" required autofocus
                   class="w-full rounded border-gray-300 mb-4">
            <button type="submit" class="w-full bg-gray-900 text-white rounded px-4 py-2">
                {{ trans('ip.submit') }}
            </button>
        </form>
    </div>
</body>
</html>
