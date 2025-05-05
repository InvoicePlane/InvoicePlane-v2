<?php

namespace App\IpModules\Merchant\Support\Drivers;

use App\IpModules\Invoices\Models\Invoice;
use App\IpModules\Merchant\Models\PaymentTypeClient;
use App\IpModules\Merchant\Models\PaymentTypePayment;
use App\IpModules\Merchant\Support\MerchantDriver;
use App\IpModules\Payments\Models\Payment;
use Exception;
use Stripe\Charge;
use Stripe\Customer;

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

            PaymentTypePayment::saveByKey($this->getName(), $payment->id, 'id', $charge->id);

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
