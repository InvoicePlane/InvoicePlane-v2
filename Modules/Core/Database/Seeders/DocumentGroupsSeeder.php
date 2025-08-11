<?php

namespace Modules\Core\Database\Seeders;

use Modules\Core\Enums\DocumentGroupType;
use Modules\Core\Models\Company;
use Modules\Core\Models\DocumentGroup;

class DocumentGroupsSeeder extends AbstractSeeder
{
    protected string $label = 'DocumentGroups';

    protected int $defaultCount = 8;

    protected function buildOne(): void
    {
        $company = Company::query()->find($this->companyId);

        if ( ! $company) {
            return;
        }

        foreach (DocumentGroupType::cases() as $type) {
            $name   = $this->getDefaultNameForType($type);
            $format = $this->getDefaultFormatForType($type);

            $exists = DocumentGroup::query()
                ->where([
                    'company_id' => $company->id,
                    'name'       => $name,
                ])
                ->exists();

            if ( ! $exists) {
                DocumentGroup::factory()
                    ->state([
                        'company_id'              => $company->id,
                        'name'                    => $name,
                        'type'                    => $type->value,
                        'group_identifier_format' => $format,
                        'next_id'                 => 1,
                        'left_pad'                => 0,
                        'format'                  => $format,
                        'reset_number'            => 0,
                        'last_id'                 => 0,
                        'last_year'               => now()->year,
                        'last_month'              => now()->month,
                        'last_week'               => now()->weekOfYear,
                    ])
                    ->create();
            }
        }
    }

    private function getDefaultNameForType(DocumentGroupType $type): string
    {
        return match($type) {
            DocumentGroupType::CREDIT_NOTES       => 'Credit Notes',
            DocumentGroupType::CUSTOMERS          => 'Customer Documents',
            DocumentGroupType::DRAFTS             => 'Draft Documents',
            DocumentGroupType::INVOICES           => 'Standard Invoices',
            DocumentGroupType::PRO_FORMA_INVOICES => 'Pro Forma Invoices',
            DocumentGroupType::PROSPECTS          => 'Prospect Documents',
            DocumentGroupType::QUOTES             => 'Standard Quotes',
            DocumentGroupType::RECURRING_INVOICES => 'Recurring Invoices',
            default                               => $type->label() . ' Documents',
        };
    }

    private function getDefaultFormatForType(DocumentGroupType $type): string
    {
        return match($type) {
            DocumentGroupType::CREDIT_NOTES       => 'CN-{YEAR}-{ID}',
            DocumentGroupType::CUSTOMERS          => 'CUST-{YEAR}-{ID}',
            DocumentGroupType::DRAFTS             => 'DRAFT-{YEAR}-{ID}',
            DocumentGroupType::INVOICES           => 'INV-{YEAR}-{ID}',
            DocumentGroupType::PROSPECTS          => 'PROS-{YEAR}-{ID}',
            DocumentGroupType::PRO_FORMA_INVOICES => 'PFI-{YEAR}-{ID}',
            DocumentGroupType::QUOTES             => 'QUO-{YEAR}-{ID}',
            DocumentGroupType::RECURRING_INVOICES => 'RECUR-{YEAR}-{ID}',
            default                               => $type->prefix() . '-{YEAR}-{ID}',
        };
    }
}
