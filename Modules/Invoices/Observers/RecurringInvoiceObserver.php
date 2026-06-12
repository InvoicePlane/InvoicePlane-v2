<?php

namespace Modules\Invoices\Observers;

use Modules\Invoices\Models\RecurringInvoice;

class RecurringInvoiceObserver
{
    /**
     * Handle the RecurringInvoice "created" event.
     */
    public function created(RecurringInvoice $recurringinvoiceobserver): void {}

    /**
     * Handle the RecurringInvoice "updated" event.
     */
    public function updated(RecurringInvoice $recurringinvoiceobserver): void {}

    /**
     * Handle the RecurringInvoice "deleted" event.
     */
    public function deleted(RecurringInvoice $recurringinvoiceobserver): void {}

    /**
     * Handle the RecurringInvoice "restored" event.
     */
    public function restored(RecurringInvoice $recurringinvoiceobserver): void {}

    /**
     * Handle the RecurringInvoice "force deleted" event.
     */
    public function forceDeleted(RecurringInvoice $recurringinvoiceobserver): void {}

    /*public static function boot(): void
    {
        parent::boot();

        static::creating(function ($recurringInvoice): void {
            event(new RecurringInvoiceCreating($recurringInvoice));
        });

        static::created(function ($recurringInvoice): void {
            event(new RecurringInvoiceCreated($recurringInvoice));
        });

        static::deleted(function ($recurringInvoice): void {
            event(new RecurringInvoiceDeleted($recurringInvoice));
        });
    }*/
}
