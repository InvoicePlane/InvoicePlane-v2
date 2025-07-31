<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Support\Facades\Log;

class DocumentGroupsSeeder extends \Modules\Core\Database\Seeders\AbstractSeeder
{
    public function run(?int $companyId = null): void
    {
        $company = $companyId
            ? \Modules\Core\Models\Company::query()->find($companyId)
            : \Modules\Core\Models\Company::where('is_active', true)->first();

        if ( ! $company) {
            Log::debug('No company found. Please run CompaniesSeeder first.');

            return;
        }

        $documentGroupTypes = \Modules\Core\Enums\DocumentGroupType::cases();
        $created            = 0;
        $now                = now();

        foreach ($documentGroupTypes as $type) {
            $name   = $this->getDefaultNameForType($type);
            $format = $this->getDefaultFormatForType($type);

            $documentGroup = \Modules\Core\Models\DocumentGroup::where([
                'company_id' => $company->id,
                'name'       => $name,
            ])->first();

            if ( ! $documentGroup) {
                \Modules\Core\Models\DocumentGroup::create(
                    [
                        'company_id'              => $company->id,
                        'name'                    => $name,
                        'type'                    => $type->value,
                        'group_identifier_format' => $format,
                        'next_id'                 => 1,
                        'left_pad'                => 0,
                        'format'                  => $format,
                        'reset_number'            => 0,
                        'last_id'                 => 0,
                        'last_year'               => $now->year,
                        'last_month'              => $now->month,
                        'last_week'               => $now->weekOfYear,
                    ]
                );
                $created++;
            }

            Log::info("Created {$created} document groups for company: {$company->name} (ID: {$company->id})");
        }
    }

    private function getDefaultNameForType(\Modules\Core\Enums\DocumentGroupType $type): string
    {
        return match($type) {
            \Modules\Core\Enums\DocumentGroupType::CREDIT_NOTES       => 'Credit Notes',
            \Modules\Core\Enums\DocumentGroupType::CUSTOMERS          => 'Customer Documents',
            \Modules\Core\Enums\DocumentGroupType::DRAFTS             => 'Draft Documents',
            \Modules\Core\Enums\DocumentGroupType::INVOICES           => 'Standard Invoices',
            \Modules\Core\Enums\DocumentGroupType::PRO_FORMA_INVOICES => 'Pro Forma Invoices',
            \Modules\Core\Enums\DocumentGroupType::PROSPECTS          => 'Prospect Documents',
            \Modules\Core\Enums\DocumentGroupType::QUOTES             => 'Standard Quotes',
            \Modules\Core\Enums\DocumentGroupType::RECURRING_INVOICES => 'Recurring Invoices',
            default                                                   => $type->label() . ' Documents',
        };
    }

    private function getDefaultFormatForType(\Modules\Core\Enums\DocumentGroupType $type): string
    {
        return match($type) {
            \Modules\Core\Enums\DocumentGroupType::CREDIT_NOTES       => 'CN-{YEAR}-{ID}',
            \Modules\Core\Enums\DocumentGroupType::CUSTOMERS          => 'CUST-{YEAR}-{ID}',
            \Modules\Core\Enums\DocumentGroupType::DRAFTS             => 'DRAFT-{YEAR}-{ID}',
            \Modules\Core\Enums\DocumentGroupType::INVOICES           => 'INV-{YEAR}-{ID}',
            \Modules\Core\Enums\DocumentGroupType::PROSPECTS          => 'PROS-{YEAR}-{ID}',
            \Modules\Core\Enums\DocumentGroupType::PRO_FORMA_INVOICES => 'PFI-{YEAR}-{ID}',
            \Modules\Core\Enums\DocumentGroupType::QUOTES             => 'QUO-{YEAR}-{ID}',
            \Modules\Core\Enums\DocumentGroupType::RECURRING_INVOICES => 'RECUR-{YEAR}-{ID}',
            default                                                   => $type->prefix() . '-{YEAR}-{ID}',
        };
    }
}
