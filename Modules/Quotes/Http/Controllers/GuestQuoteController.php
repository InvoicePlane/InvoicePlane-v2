<?php

namespace Modules\Quotes\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Illuminate\View\View as ViewContract;
use InvalidArgumentException;
use Modules\Quotes\Models\Quote;
use Modules\Quotes\Services\QuoteService;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GuestQuoteController extends Controller
{
    public function show(Quote $quote): ViewContract
    {
        if ($this->isPasswordRequired($quote)) {
            return View::make('quotes::guest.password', ['quote' => $quote]);
        }

        return View::make('quotes::guest.show', [
            'quote'     => $quote,
            'html'      => app(QuoteService::class)->renderHtml($quote),
            'signature' => $quote->signatures()->latest('signed_at')->first(),
        ]);
    }

    public function verifyPassword(Request $request, Quote $quote): RedirectResponse
    {
        $data = $request->validate(['password' => ['required', 'string']]);

        if ( ! hash_equals((string) $quote->quote_password, $data['password'])) {
            return Redirect::route('quotes.guest.show', $quote)
                ->withErrors(['password' => trans('ip.quote_password_incorrect')]);
        }

        $request->session()->put($this->sessionKey($quote), true);

        return Redirect::route('quotes.guest.show', $quote);
    }

    public function sign(Request $request, Quote $quote): RedirectResponse
    {
        abort_if($this->isPasswordRequired($quote), 403);

        $data = $request->validate([
            'signer_name'    => ['required', 'string', 'max:255'],
            'signature_data' => ['required', 'string'],
        ]);

        /*
         * captureSignature() itself allows multiple signatures per quote
         * (used elsewhere for multi-signer flows), so the guest "sign once"
         * rule is enforced here, not in the service. Locking the row inside
         * a transaction closes the gap between the isSigned() check and the
         * insert so two concurrent guest submits can't both get through.
         */
        try {
            $alreadySigned = DB::transaction(function () use ($request, $quote, $data): bool {
                $locked = Quote::query()->whereKey($quote->id)->lockForUpdate()->firstOrFail();

                if ($locked->isSigned()) {
                    return true;
                }

                app(QuoteService::class)->captureSignature(
                    $quote,
                    $data['signature_data'],
                    $data['signer_name'],
                    userId: null,
                    ipAddress: $request->ip(),
                    userAgent: $request->userAgent(),
                );

                return false;
            });
        } catch (InvalidArgumentException|RuntimeException $e) {
            return Redirect::route('quotes.guest.show', $quote)
                ->withErrors(['signature_data' => $e->getMessage()]);
        }

        abort_if($alreadySigned, 403, trans('ip.quote_already_signed'));

        return Redirect::route('quotes.guest.show', $quote)
            ->with('status', trans('ip.quote_signed_successfully'));
    }

    public function pdf(Quote $quote): StreamedResponse
    {
        abort_if($this->isPasswordRequired($quote), 403);

        return app(QuoteService::class)->generatePdf($quote);
    }

    private function isPasswordRequired(Quote $quote): bool
    {
        if (empty($quote->quote_password)) {
            return false;
        }

        return session($this->sessionKey($quote)) !== true;
    }

    private function sessionKey(Quote $quote): string
    {
        return "quote_access.{$quote->id}";
    }
}
