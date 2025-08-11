<?php

namespace Modules\Core\Services;

class CompanyDefaultsBootstrapService
{
    public static function bootstrap(int $companyId): void
    {
        $company = \Modules\Core\Models\Company::findOrFail($companyId);

        // Create default document group for invoices
        $documentGroupData = [
            'company_id'              => $company->id,
            'type'                    => \Modules\Core\Enums\DocumentGroupType::INVOICES->value,
            'group_identifier_format' => \Modules\Core\Enums\DocumentGroupType::INVOICES->prefix() . '-{YEAR}-{MONTH}-{ID}',
            'name'                    => \Modules\Core\Enums\DocumentGroupType::INVOICES->label(),
            'left_pad'                => 6,
            'format'                  => \Modules\Core\Enums\DocumentGroupType::INVOICES->prefix() . '-{YEAR}-{MONTH}-{ID}',
            'next_id'                 => 1,
            'reset_number'            => 0,
            'last_id'                 => 0,
            'last_year'               => now()->year,
            'last_month'              => now()->month,
            'last_week'               => now()->week,
        ];

        \Modules\Core\Models\DocumentGroup::firstOrCreate(
            [
                'company_id' => $company->id,
                'type'       => \Modules\Core\Enums\DocumentGroupType::INVOICES->value,
                'name'       => \Modules\Core\Enums\DocumentGroupType::INVOICES->label(),
            ],
            $documentGroupData
        );

        // Create default email template
        \Modules\Core\Models\EmailTemplate::firstOrCreate(
            [
                'company_id' => $company->id,
                'title'      => 'Default Template',
            ],
            [
                'subject'    => 'Invoice #{invoice.number}',
                'body'       => 'Please find your invoice attached.',
                'from_name'  => $company->name,
                'from_email' => 'billing@' . mb_strtolower(preg_replace('/[^A-Za-z0-9]/', '', $company->name)) . '.com',
                'cc'         => null,
                'bcc'        => null,
            ]
        );

        // Create default tax rate
        \Modules\Core\Models\TaxRate::firstOrCreate(
            [
                'company_id'    => $company->id,
                'name'          => 'Standard VAT',
                'code'          => 'VAT21',
                'tax_rate_type' => \Modules\Core\Enums\TaxRateType::EXCLUSIVE->value,
            ],
            [
                'rate' => 21.00,
            ]
        );

        // Create default product category
        \Modules\Products\Models\ProductCategory::firstOrCreate(
            [
                'company_id'    => $company->id,
                'category_name' => 'General',
            ],
            [
            ]
        );

        // Create default product unit
        \Modules\Products\Models\ProductUnit::firstOrCreate(
            [
                'company_id' => $company->id,
                'unit_name'  => 'Piece',
            ],
            [
                'unit_name_plrl' => 'Pieces',
            ]
        );

        // Create default expense category
        \Modules\Expenses\Models\ExpenseCategory::firstOrCreate(
            [
                'company_id'    => $company->id,
                'category_name' => 'Office Expenses',
            ],
            [
            ]
        );
    }
}
