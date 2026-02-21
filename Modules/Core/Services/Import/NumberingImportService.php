<?php

namespace Modules\Core\Services\Import;

use Modules\Core\Models\Numbering;

class NumberingImportService extends AbstractImportService
{
    public function getTables(): array
    {
        return ['ip_invoice_groups'];
    }

    public function import(int $companyId, array &$idMappings): array
    {
        $this->companyId = $companyId;
        $this->idMappings = &$idMappings;
        $this->initStats(['invoice_groups']);

        $this->importInvoiceGroups();

        return $this->stats;
    }

    private function importInvoiceGroups(): void
    {
        $groups = $this->getImportData('ip_invoice_groups');

        foreach ($groups as $group) {
            $numbering = Numbering::create([
                'company_id' => $this->companyId,
                'type'       => 'invoice',
                'name'       => $group->invoice_group_name,
                'next_id'    => $group->invoice_group_next_id ?? 1,
                'left_pad'   => 0,
                'format'     => $group->invoice_group_prefix ?? 'INV',
                'prefix'     => $group->invoice_group_prefix ?? 'INV',
            ]);

            $this->idMappings['invoice_groups'][$group->invoice_group_id] = $numbering->id;
            $this->stats['invoice_groups']++;
        }
    }
}
