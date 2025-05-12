<?php

namespace Modules\Payments\Support\Drivers;

use Exception;
use Modules\Clients\Models\Customer;
use Modules\Clients\Models\Relation;
use Modules\Invoices\Models\Invoice;
use Modules\Payments\Models\MerchantClient;
use Modules\Payments\Models\MerchantPayment;
use Modules\Payments\Models\Payment;
use Modules\Payments\Support\MerchantDriver;

class Stripe extends MerchantDriver
{
    protected $isRedirect = false;

    public function getSettings()
    {
        return ['publishableKey', 'secretKey'];
    }

    public function verify(Invoice $invoice)
    {
        \Stripe\Stripe::setApiKey($this->getSetting('secretKey'));

        $clientMerchantId = MerchantClient::getByKey($this->getName(), $invoice->customer_id, 'id');

        if ($clientMerchantId) {
            try {
                $customer = Relation::retrieve($clientMerchantId);
            } catch (Exception $e) {
                // Don't need to do anything here.
            }
        }

        if ( ! isset($customer) || $customer->deleted) {
            $customer = $this->createCustomer($invoice, request('token'));
        } else {
            $customer->source = request('token');
            $customer->save();
        }

        try {
            $charge = Charge::create([
                'customer'    => $customer->id,
                'amount'      => $invoice->amount->balance * 100,
                'currency'    => $invoice->currency_code,
                'description' => trans('ip.invoice') . ' #' . $invoice->number,
            ]);

            $payment = Payment::create([
                'invoice_id'     => $invoice->id,
                'amount'         => $charge->amount / 100,
                'payment_method' => config('ip.onlinePaymentMethod'),
            ]);

            MerchantPayment::saveByKey($this->getName(), $payment->id, 'id', $charge->id);

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function processPayment(Payment $payment): bool
    {
        // logic to connect Stripe API
        // Example pseudo-code:
        $response = Http::withToken($this->getSecretKey())
            ->post('https://api.stripe.com/v1/charges', [
                'amount'      => $payment->payment_amount * 100, // cents
                'currency'    => 'usd',
                'description' => 'Payment #' . $payment->id,
            ]);

        return $response->successful();
    }

    private function createCustomer($invoice, $source)
    {
        $customer = Customer::query()->create([
            'description' => $invoice->customer->name,
            'email'       => $invoice->customer->email,
            'source'      => $source,
        ]);

        MerchantClient::saveByKey($this->getName(), $invoice->customer_id, 'id', $customer->id);

        return $customer;
    }

    private function getSecretKey(): string
    {
        return config('services.stripe.secret_key');
    }
}
