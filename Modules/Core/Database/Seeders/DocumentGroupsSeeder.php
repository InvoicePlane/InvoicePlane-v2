<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Models\Company;
use Modules\Core\Models\DocumentGroup;

class DocumentGroupsSeeder extends Seeder
{
    public function run(): void
    {
        Company::all()->each(function (Company $company): void {
            // Create one document group for each document type
            foreach (\Modules\Core\Enums\DocumentGroupType::cases() as $type) {
                DocumentGroup::factory()
                    ->for($company)
                    ->create([
                        'type'                    => $type->value,
                        'name'                    => $this->getDefaultNameForType($type),
                        'format'                  => $this->getDefaultFormatForType($type),
                        'group_identifier_format' => $this->getDefaultFormatForType($type),
                    ]);
            }

            // Optionally create some additional random document groups
            if (random_int(0, 1) === 1) {
                DocumentGroup::factory(random_int(1, 2))
                    ->for($company)
                    ->create();
            }
        });
    }

    private function getDefaultNameForType(\Modules\Core\Enums\DocumentGroupType $type): string
    {
        return match($type) {
            \Modules\Core\Enums\DocumentGroupType::INVOICES           => 'Standard Invoices',
            \Modules\Core\Enums\DocumentGroupType::QUOTES             => 'Standard Quotes',
            \Modules\Core\Enums\DocumentGroupType::PRO_FORMA_INVOICES => 'Pro Forma Invoices',
            \Modules\Core\Enums\DocumentGroupType::CREDIT_NOTES       => 'Credit Notes',
            \Modules\Core\Enums\DocumentGroupType::RECURRING_INVOICES => 'Recurring Invoices',
            \Modules\Core\Enums\DocumentGroupType::DRAFTS             => 'Draft Documents',
            \Modules\Core\Enums\DocumentGroupType::CUSTOMERS          => 'Customer Documents',
            \Modules\Core\Enums\DocumentGroupType::PROSPECTS          => 'Prospect Documents',
            default                                                   => $type->label() . ' Documents',
        };
    }

    private function getDefaultFormatForType(\Modules\Core\Enums\DocumentGroupType $type): string
    {
        return match($type) {
            \Modules\Core\Enums\DocumentGroupType::INVOICES           => 'INV-{YEAR}-{ID}',
            \Modules\Core\Enums\DocumentGroupType::QUOTES             => 'QUO-{YEAR}-{ID}',
            \Modules\Core\Enums\DocumentGroupType::PRO_FORMA_INVOICES => 'PFI-{YEAR}-{ID}',
            \Modules\Core\Enums\DocumentGroupType::CREDIT_NOTES       => 'CN-{YEAR}-{ID}',
            \Modules\Core\Enums\DocumentGroupType::RECURRING_INVOICES => 'RINV-{YEAR}-{ID}',
            \Modules\Core\Enums\DocumentGroupType::DRAFTS             => 'DRAFT-{YEAR}-{ID}',
            \Modules\Core\Enums\DocumentGroupType::CUSTOMERS          => 'CUST-{YEAR}-{ID}',
            \Modules\Core\Enums\DocumentGroupType::PROSPECTS          => 'PROS-{YEAR}-{ID}',
            default                                                   => $type->prefix() . '-{YEAR}-{ID}',
        };
    }
}
