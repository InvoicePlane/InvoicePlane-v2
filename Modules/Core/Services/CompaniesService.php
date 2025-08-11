<?php

namespace Modules\Core\Services;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Models\Company;

class CompaniesService extends BaseService
{
    public function model(): string
    {
        return Company::class;
    }

    public function createCompany(array $data): Model
    {
        $company = Company::query()->create([
            'search_code'      => $data['search_code'] ?? 'search_code_not_found',
            'name'             => $data['name'] ?? 'name not found',
            'slug'             => $data['slug'] ?? 'slug-not-found',
            'vat_number'       => $data['vat_number'] ?? null,
            'id_number'        => $data['id_number'] ?? null,
            'coc_number'       => $data['coc_number'] ?? null,
            'quote_template'   => $data['quote_template'] ?? 'default',
            'invoice_template' => $data['invoice_template'] ?? 'default',
        ]);

        return $company;
    }

    public function updateCompany($company, array $data): Model
    {
        $updateData = [
            'name' => $data['name'],
        ];

        $company->update($updateData);

        return $company;
    }
}
