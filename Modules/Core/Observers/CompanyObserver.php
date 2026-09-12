<?php

namespace Modules\Core\Observers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\Core\Models\Company;
use Modules\Core\Services\CompanyDefaultsBootstrapService;

class CompanyObserver
{
    public function created(Company $company): void
    {
        (new CompanyDefaultsBootstrapService())->bootstrap($company->id);

        Log::info('Bootstrapped default data for company', [
            'company_id'   => $company->id,
            'company_name' => $company->name,
        ]);
    }

    public function updated(Company $company): void {}

    public function deleted(Company $company): void
    {
        // Per-company report storage lives outside the database; nothing else
        // reaps it when the company row goes.
        Storage::disk('report_templates')->deleteDirectory((string) $company->id);
        Storage::disk('report_pdfs')->deleteDirectory((string) $company->id);
    }

    public function restored(Company $company): void {}

    public function forceDeleted(Company $company): void {}

    /*    public static function boot(): void
        {
            parent::boot();

            static::saving(function ($companyProfile): void {
                //event(new CompanyProfileSaving($companyProfile));
            });

            static::creating(function ($companyProfile): void {
                //event(new CompanyProfileCreating($companyProfile));
            });

            static::created(function ($company): void {
                // This is now handled by the observer's created() method
            });

            static::deleted(function ($companyProfile): void {
                //event(new CompanyProfileDeleted($companyProfile));
            });
        }*/
}
