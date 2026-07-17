<?php

namespace Modules\Core\Services;

use Modules\Core\Enums\NumberingType;
use Modules\Core\Enums\TaxRateType;
use Modules\Core\Models\Company;
use Modules\Core\Models\EmailTemplate;
use Modules\Core\Models\Numbering;
use Modules\Core\Models\TaxRate;
use Modules\Expenses\Models\ExpenseCategory;
use Modules\Products\Models\ProductCategory;
use Modules\Products\Models\ProductUnit;

class CompanyDefaultsBootstrapService
{
    public static function bootstrap(int $companyId): void
    {
        $company = Company::findOrFail($companyId);

        // Create default numbering for invoices
        $numberingData = [
            'company_id'              => $company->id,
            'type'                    => NumberingType::INVOICE->value,
            'group_identifier_format' => NumberingType::INVOICE->prefix() . '-{YEAR}-{MONTH}-{ID}',
            'name'                    => NumberingType::INVOICE->label(),
            'left_pad'                => 6,
            'format'                  => NumberingType::INVOICE->prefix() . '-{YEAR}-{MONTH}-{ID}',
            'next_id'                 => 1,
            'reset_number'            => 0,
            'last_id'                 => 0,
            'last_year'               => now()->year,
            'last_month'              => now()->month,
            'last_week'               => now()->week,
        ];

        Numbering::query()->firstOrCreate(
            [
                'company_id' => $company->id,
                'type'       => NumberingType::INVOICE->value,
                'name'       => NumberingType::INVOICE->label(),
            ],
            $numberingData
        );

        // Create default email template
        EmailTemplate::query()->firstOrCreate(
            [
                'company_id' => $company->id,
                'title'      => 'invoice_sent',
            ],
            [
                'subject'    => 'Invoice #{{ invoice.number }}',
                'body'       => 'Please find your invoice #{{ invoice.number }} attached.',
                'from_name'  => $company->name,
                'from_email' => 'billing@' . mb_strtolower(preg_replace('/[^A-Za-z0-9]/', '', $company->name)) . '.com',
                'cc'         => null,
                'bcc'        => null,
            ]
        );

        // Create default tax rate
        TaxRate::query()->firstOrCreate(
            [
                'company_id'    => $company->id,
                'name'          => 'Standard VAT',
                'code'          => 'VAT21',
                'tax_rate_type' => TaxRateType::EXCLUSIVE->value,
            ],
            [
                'rate' => 21.00,
            ]
        );

        // Create default product category
        ProductCategory::query()->firstOrCreate(
            [
                'company_id'    => $company->id,
                'category_name' => 'General',
            ],
            [
            ]
        );

        // Create default product unit
        ProductUnit::query()->firstOrCreate(
            [
                'company_id' => $company->id,
                'unit_name'  => 'Piece',
            ],
            [
                'unit_name_plrl' => 'Pieces',
            ]
        );

        // Create default expense category
        ExpenseCategory::query()->firstOrCreate(
            [
                'company_id'    => $company->id,
                'category_name' => 'Office Expenses',
            ],
            [
            ]
        );
    }
}
