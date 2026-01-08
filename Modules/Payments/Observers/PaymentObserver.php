<?php

namespace Modules\Payments\Observers;

use Modules\Core\Observers\AbstractObserver;

class PaymentObserver extends AbstractObserver
{
    /*public static function boot(): void
    {
        parent::boot();

        self::created(function ($payment): void {
            //event(new InvoiceModified($payment->invoice));
            //event(new PaymentCreated($payment));
        });

        self::creating(function ($payment): void {
            //event(new PaymentCreating($payment));
        });

        self::updated(function ($payment): void {
            //event(new InvoiceModified($payment->invoice));
        });

        self::deleting(function ($payment): void {
            foreach ($payment->mailQueue as $mailQueue) {
                $mailQueue->delete();
            }

            //$payment->custom()->delete();
        });

        self::deleted(function ($payment): void {
            if ($payment->invoice) {
                //event(new InvoiceModified($payment->invoice));
            }
        });
    }*/
}
