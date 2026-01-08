<?php

namespace Modules\Expenses\Observers;

use Modules\Core\Observers\AbstractObserver;

class ExpenseObserver extends AbstractObserver
{
    /*
     * The actual creating() gets done in the Abstract
     */
    /*public static function boot(): void
    {
        parent::boot();

        static::created(function ($expense): void {
            //event(new ExpenseCreated($expense));
        });

        static::saved(function ($expense): void {
            //event(new CheckAttachment($expense));
        });

        static::saving(function ($expense): void {
            //event(new ExpenseSaving($expense));
        });

        static::deleting(function ($expense): void {
            event(new ExpenseDeleting($expense));
        });
    }*/
}
