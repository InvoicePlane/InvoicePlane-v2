<?php

namespace Modules\Core\Services;

use Illuminate\Support\Facades\DB;
use Modules\Core\Models\Company;
use Throwable;

class CompanyService extends BaseService
{
    public function model(): string
    {
        return Company::class;
    }

    public function createCompany(array $data): Company
    {
        DB::beginTransaction();

        try {
            /** @var Company $company */
            $company = Company::query()->create([
                'search_code'      => $data['search_code'],
                'name'             => $data['name'],
                'slug'             => $data['slug'],
                'vat_number'       => $data['vat_number'] ?? null,
                'id_number'        => $data['id_number'] ?? null,
                'coc_number'       => $data['coc_number'] ?? null,
                'logo'             => $data['logo'] ?? null,
                'quote_template'   => $data['quote_template'] ?? null,
                'invoice_template' => $data['invoice_template'] ?? null,
            ]);

            DB::commit();

            return $company->refresh();
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateCompany(Company $company, array $data): Company
    {
        DB::beginTransaction();

        try {
            $company->update([
                'search_code'      => $data['search_code'],
                'name'             => $data['name'],
                'slug'             => $data['slug'],
                'vat_number'       => $data['vat_number'] ?? null,
                'id_number'        => $data['id_number'] ?? null,
                'coc_number'       => $data['coc_number'] ?? null,
                'logo'             => $data['logo'] ?? null,
                'quote_template'   => $data['quote_template'] ?? null,
                'invoice_template' => $data['invoice_template'] ?? null,
            ]);

            DB::commit();

            return $company->refresh();
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
