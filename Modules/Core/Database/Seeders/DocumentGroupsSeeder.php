<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;

class DocumentGroupsSeeder extends Seeder
{
    public function run(): void {}

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
