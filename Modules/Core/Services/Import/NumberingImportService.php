<?php

namespace Modules\Core\Services\Import;

use Illuminate\Support\Facades\DB;
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

    /**
     * Apply proper numbering logic after invoices and quotes are imported
     * This ensures numberings reflect the actual state and won't fail
     */
    public function applyNumberingLogic(int $companyId): void
    {
        $numberings = Numbering::where('company_id', $companyId)
            ->where('type', 'invoice')
            ->get();

        foreach ($numberings as $numbering) {
            // Get the highest invoice number for this numbering
            $maxInvoiceNumber = DB::table('invoices')
                ->where('company_id', $companyId)
                ->where('numbering_id', $numbering->id)
                ->whereNotNull('invoice_number')
                ->max('invoice_number');

            if ($maxInvoiceNumber) {
                // Extract numeric part from invoice number
                $numericPart = preg_replace('/[^0-9]/', '', $maxInvoiceNumber);
                if ($numericPart) {
                    // Set next_id to be one more than the highest
                    $numbering->update([
                        'next_id' => (int) $numericPart + 1,
                    ]);
                }
            }
        }

        // Apply similar logic for quote numberings
        $quoteNumberings = Numbering::where('company_id', $companyId)
            ->where('type', 'quote')
            ->get();

        foreach ($quoteNumberings as $numbering) {
            $maxQuoteNumber = DB::table('quotes')
                ->where('company_id', $companyId)
                ->where('numbering_id', $numbering->id)
                ->whereNotNull('quote_number')
                ->max('quote_number');

            if ($maxQuoteNumber) {
                $numericPart = preg_replace('/[^0-9]/', '', $maxQuoteNumber);
                if ($numericPart) {
                    $numbering->update([
                        'next_id' => (int) $numericPart + 1,
                    ]);
                }
            }
        }
    }
}
