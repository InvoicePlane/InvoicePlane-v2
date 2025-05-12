<?php

namespace Modules\Payments\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Modules\Payments\Models\Payment;

class ProcessMerchantPaymentJob implements ShouldQueue
{
    public function __construct(public Payment $payment) {}

    public function handle(): void
    {
        $merchantName = $this->payment->merchantClient?->name;

        if ( ! $merchantName) {
            // Handle as simple payment without external merchant
            return;
        }

        $merchantClient = MerchantClientFactory::make($merchantName);

        $success = $merchantClient->processPayment($this->payment);

        if ( ! $success) {
            // handle failure
            Log::error("Payment processing failed for ID: {$this->payment->id}");
        } else {
            // handle success
            $this->payment->update(['payment_status' => 'completed']);
        }
    }
}
