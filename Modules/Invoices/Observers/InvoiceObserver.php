<?php

namespace Modules\Invoices\Observers;

use Modules\Core\Observers\AbstractObserver;

class InvoiceObserver extends AbstractObserver
{
    /*public static function boot(): void
    {
        parent::boot();

        static::creating(function ($invoice): void {
            //event(new InvoiceCreating($invoice));
        });

        static::created(function ($invoice): void {
            //event(new InvoiceCreated($invoice));
        });

        static::deleted(function ($invoice): void {
            //event(new InvoiceDeleted($invoice));
        });
    }*/
}
