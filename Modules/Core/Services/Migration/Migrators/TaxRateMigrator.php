<?php

namespace Modules\Core\Services\Migration\Migrators;

use Modules\Core\Enums\TaxRateType;
use Modules\Core\Models\TaxRate;
use Modules\Core\Services\Migration\Contracts\EntityMigratorInterface;
use Modules\Core\Services\Migration\MigrationContext;

class TaxRateMigrator implements EntityMigratorInterface
{
    public function name(): string
    {
        return 'tax_rates';
    }

    public function label(): string
    {
        return 'Tax Rates';
    }

    public function inspect(MigrationContext $context): array
    {
        $rows = $context->getSourceTable('tax_rates');
        $notes = [];

        $willMigrate = 0;
        $unmappable = 0;

        foreach ($rows as $row) {
            $name = trim((string) ($row['tax_rate_name'] ?? ''));
            if ($name === '') {
                $unmappable++;
                $notes[] = "Tax rate row #{$row['tax_rate_id']} has empty name, will be skipped.";
            } else {
                $willMigrate++;
            }
        }

        return [
            'source_count' => $rows->count(),
            'will_migrate' => $willMigrate,
            'unmappable'   => $unmappable,
            'notes'        => $notes,
        ];
    }

    public function migrate(MigrationContext $context): array
    {
        $rows = $context->getSourceTable('tax_rates');
        $migrated = 0;
        $skipped = 0;
        $errors = [];

        foreach ($rows as $row) {
            $v1Id = $row['tax_rate_id'] ?? null;
            $name = trim((string) ($row['tax_rate_name'] ?? ''));
            $rate = (float) ($row['tax_rate_percent'] ?? 0.0);
            $code = !empty($row['tax_rate_code']) ? (string) $row['tax_rate_code'] : strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $name) ?: 'TAX', 0, 10));

            if ($name === '') {
                $skipped++;
                continue;
            }

            if ($context->isDryRun()) {
                if ($v1Id !== null) {
                    $context->mapId('tax_rates', $v1Id, (int) $v1Id);
                }
                $migrated++;
                continue;
            }

            try {
                // Idempotency check: see if a tax rate with same name/rate exists for company
                $taxRate = TaxRate::withoutGlobalScopes()
                    ->where('company_id', $context->getCompanyId())
                    ->where('name', $name)
                    ->first();

                if (!$taxRate) {
                    $taxRate = TaxRate::create([
                        'company_id'    => $context->getCompanyId(),
                        'name'          => $name,
                        'rate'          => $rate,
                        'code'          => $code,
                        'is_active'     => true,
                        'is_compound'   => (bool) ($row['tax_rate_is_compound'] ?? false),
                        'calculate_vat' => (bool) ($row['tax_rate_calculate_vat'] ?? false),
                        'tax_rate_type' => TaxRateType::EXCLUSIVE,
                    ]);
                    $context->recordCreated(TaxRate::class, $taxRate->id);
                } else {
                    $taxRate->update([
                        'rate'          => $rate,
                        'code'          => $code,
                        'is_compound'   => (bool) ($row['tax_rate_is_compound'] ?? false),
                        'calculate_vat' => (bool) ($row['tax_rate_calculate_vat'] ?? false),
                    ]);
                }

                if ($v1Id !== null) {
                    $context->mapId('tax_rates', $v1Id, $taxRate->id);
                }

                $migrated++;
            } catch (\Throwable $e) {
                $errors[] = "Failed to migrate tax rate '{$name}': " . $e->getMessage();
                $skipped++;
            }
        }

        $context->log("Migrated {$migrated} tax rates ({$skipped} skipped).");

        return [
            'migrated' => $migrated,
            'skipped'  => $skipped,
            'errors'   => $errors,
        ];
    }

    public function rollback(MigrationContext $context): int
    {
        $ids = $context->getCreatedIds(TaxRate::class);
        if (empty($ids)) {
            return 0;
        }

        return TaxRate::withoutGlobalScopes()
            ->whereIn('id', $ids)
            ->where('company_id', $context->getCompanyId())
            ->delete();
    }
}
