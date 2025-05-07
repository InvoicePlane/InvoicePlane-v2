<?php

namespace Modules\Core\Support\Drivers;

use Modules\Core\Support\Results\Payments;

use Modules\Payments\Models\Payment;

use Modules\Invoices\Models\Invoice;

use Exception;
use Modules\Core\Support\MerchantDriver;

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

        $clientMerchantId = PaymentTypeClient::getByKey($this->getName(), $invoice->customer_id, 'id');

        if ($clientMerchantId) {
            try {
                $customer = Customer::retrieve($clientMerchantId);
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
                'invoice_id'        => $invoice->id,
                'amount'            => $charge->amount / 100,
                'payment_method_id' => config('ip.onlinePaymentMethod'),
            ]);

            MerchantPayment::saveByKey($this->getName(), $payment->id, 'id', $charge->id);

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    private function createCustomer($invoice, $source)
    {
        $customer = Customer::create([
            'description' => $invoice->customer->name,
            'email'       => $invoice->customer->email,
            'source'      => $source,
        ]);

        PaymentTypeClient::saveByKey($this->getName(), $invoice->customer_id, 'id', $customer->id);

        return $customer;
    }
}
