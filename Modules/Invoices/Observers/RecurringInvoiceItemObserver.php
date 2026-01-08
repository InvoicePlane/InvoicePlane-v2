<?php

namespace Modules\Invoices\Observers;

use Modules\Invoices\Models\RecurringInvoiceItem;

class RecurringInvoiceItemObserver
{
    /**
     * Handle the RecurringInvoiceItem "created" event.
     */
    public function created(RecurringInvoiceItem $recurringinvoiceitem): void {}

    /**
     * Handle the RecurringInvoiceItem "updated" event.
     */
    public function updated(RecurringInvoiceItem $recurringinvoiceitem): void {}

    /**
     * Handle the RecurringInvoiceItem "deleted" event.
     */
    public function deleted(RecurringInvoiceItem $recurringinvoiceitem): void {}

    /**
     * Handle the RecurringInvoiceItem "restored" event.
     */
    public function restored(RecurringInvoiceItem $recurringinvoiceitem): void {}

    /**
     * Handle the RecurringInvoiceItem "force deleted" event.
     */
    public function forceDeleted(RecurringInvoiceItem $recurringinvoiceitem): void {}

    /*public static function boot(): void
    {
        parent::boot();

        static::saving(function ($recurringInvoiceItem): void {
            event(new RecurringInvoiceItemSaving($recurringInvoiceItem));
        });

        static::saved(function ($recurringInvoiceItem): void {
            event(new RecurringInvoiceModified($recurringInvoiceItem->recurringInvoice));
        });

        static::deleting(function ($recurringInvoiceItem): void {
            $recurringInvoiceItem->amount()->delete();
        });

        static::deleted(function ($recurringInvoiceItem): void {
            if ($recurringInvoiceItem->recurringInvoice) {
                event(new RecurringInvoiceModified($recurringInvoiceItem->recurringInvoice));
            }
        });
    }*/
}
