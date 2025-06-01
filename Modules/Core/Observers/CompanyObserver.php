<?php

namespace Modules\Core\Observers;

use Modules\Core\Models\Company;

class CompanyObserver
{
    /**
     * Handle the Company "created" event.
     */
    public function created(Company $companyobserver): void {}

    /**
     * Handle the Company "updated" event.
     */
    public function updated(Company $companyobserver): void {}

    /**
     * Handle the Company "deleted" event.
     */
    public function deleted(Company $companyobserver): void {}

    /**
     * Handle the Company "restored" event.
     */
    public function restored(Company $companyobserver): void {}

    /**
     * Handle the Company "force deleted" event.
     */
    public function forceDeleted(Company $companyobserver): void {}

    /*    public static function boot(): void
        {
            parent::boot();

            static::saving(function ($companyProfile): void {
                //event(new CompanyProfileSaving($companyProfile));
            });

            static::creating(function ($companyProfile): void {
                //event(new CompanyProfileCreating($companyProfile));
            });

            static::created(function ($companyProfile): void {
                //event(new CompanyProfileCreated($companyProfile));
            });

            static::deleted(function ($companyProfile): void {
                //event(new CompanyProfileDeleted($companyProfile));
            });
        }*/
}
