<?php

namespace Modules\Core\Services\Import;

use Illuminate\Support\Facades\DB;
use Modules\Core\Enums\NumberingType;
use Modules\Core\Models\Numbering;

class NumberingImportService extends AbstractImportService
{
    public function getTables(): array
    {
        return ['ip_invoice_groups'];
    }

    public function import(int $companyId, array &$idMappings): array
    {
        $this->companyId  = $companyId;
        $this->idMappings = &$idMappings;
        $this->initStats(['invoice_groups']);

        $this->importInvoiceGroups();

        return $this->stats;
    }

    /**
     * Apply proper numbering logic after invoices and quotes are imported
     * This ensures numberings reflect the actual state and won't fail.
     */
    public function applyNumberingLogic(int $companyId): void
    {
        $numberings = Numbering::where('company_id', $companyId)
            ->where('type', NumberingType::INVOICE->value)
            ->get();

        foreach ($numberings as $numbering) {
            // Get all invoice numbers for this numbering to find highest numeric value
            $invoiceNumbers = DB::table('invoices')
                ->where('company_id', $companyId)
                ->where('numbering_id', $numbering->id)
                ->whereNotNull('invoice_number')
                ->pluck('invoice_number');

            if ($invoiceNumbers->isNotEmpty()) {
                // Extract numeric parts from all invoice numbers and find max
                $maxNumeric = $invoiceNumbers->map(function ($number) {
                    return (int) preg_replace('/[^0-9]/', '', $number);
                })->max();

                if ($maxNumeric) {
                    $numbering->update([
                        'next_id' => $maxNumeric + 1,
                    ]);
                }
            }
        }

        // Apply similar logic for quote numberings
        $quoteNumberings = Numbering::where('company_id', $companyId)
            ->where('type', NumberingType::QUOTE->value)
            ->get();

        foreach ($quoteNumberings as $numbering) {
            $quoteNumbers = DB::table('quotes')
                ->where('company_id', $companyId)
                ->where('numbering_id', $numbering->id)
                ->whereNotNull('quote_number')
                ->pluck('quote_number');

            if ($quoteNumbers->isNotEmpty()) {
                // Extract numeric parts from all quote numbers and find max
                $maxNumeric = $quoteNumbers->map(function ($number) {
                    return (int) preg_replace('/[^0-9]/', '', $number);
                })->max();

                if ($maxNumeric) {
                    $numbering->update([
                        'next_id' => $maxNumeric + 1,
                    ]);
                }
            }
        }
    }

    private function importInvoiceGroups(): void
    {
        $groups = $this->getImportData('ip_invoice_groups');

        foreach ($groups as $group) {
            $numbering = Numbering::create([
                'company_id' => $this->companyId,
                'type'       => NumberingType::INVOICE,
                'name'       => $group->invoice_group_name,
                'next_id'    => $group->invoice_group_next_id ?? 1,
                'left_pad'   => 0,
                'format'     => null,
                'prefix'     => $group->invoice_group_prefix ?? 'INV',
            ]);

            $this->idMappings['invoice_groups'][$group->invoice_group_id] = $numbering->id;
            $this->stats['invoice_groups']++;
        }
    }
}
