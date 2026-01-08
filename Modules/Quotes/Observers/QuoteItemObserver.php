<?php

namespace Modules\Quotes\Observers;

use Modules\Core\Observers\AbstractObserver;
use Modules\Quotes\Models\QuoteItem;

class QuoteItemObserver extends AbstractObserver
{
    /**
     * Handle the QuoteItem "created" event.
     */
    /*public function created(QuoteItem $quoteitem): void {}*/

    /**
     * Handle the QuoteItem "updated" event.
     */
    /*public function updated(QuoteItem $quoteitem): void {}*/

    /**
     * Handle the QuoteItem "deleted" event.
     */
    /*public function deleted(QuoteItem $quoteitem): void {}*/

    /**
     * Handle the QuoteItem "restored" event.
     */
    /*public function restored(QuoteItem $quoteitem): void {}*/

    /**
     * Handle the QuoteItem "force deleted" event.
     */
    public function forceDeleted(QuoteItem $quoteitem): void {}

    /*public static function boot(): void
    {
        parent::boot();

        static::deleting(function ($quoteItem): void {
            $quoteItem->amount()->delete();
        });

        static::deleted(function ($quoteItem): void {
            if ($quoteItem->quote) {
                //event(new QuoteModified($quoteItem->quote));
            }
        });

        static::saving(function ($quoteItem): void {
            //event(new QuoteItemSaving($quoteItem));
        });

        static::saved(function ($quoteItem): void {
            //event(new QuoteModified($quoteItem->quote));
        });
    }*/
}
