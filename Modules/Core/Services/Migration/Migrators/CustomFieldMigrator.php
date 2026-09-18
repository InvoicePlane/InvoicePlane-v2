<?php

namespace Modules\Core\Services\Migration\Migrators;

use Modules\Clients\Models\Relation;
use Modules\Core\Models\CustomField;
use Modules\Core\Models\CustomFieldValue;
use Modules\Core\Services\Migration\Contracts\EntityMigratorInterface;
use Modules\Core\Services\Migration\MigrationContext;
use Modules\Invoices\Models\Invoice;
use Modules\Quotes\Models\Quote;
use Throwable;

class CustomFieldMigrator implements EntityMigratorInterface
{
    public function name(): string
    {
        return 'custom_fields';
    }

    public function label(): string
    {
        return 'Custom Fields & Values';
    }

    public function inspect(MigrationContext $context): array
    {
        $fields = $context->getSourceTable('custom_fields');

        return [
            'source_count' => $fields->count(),
            'will_migrate' => $fields->count(),
            'unmappable'   => 0,
            'notes'        => [],
        ];
    }

    public function migrate(MigrationContext $context): array
    {
        $fields   = $context->getSourceTable('custom_fields');
        $migrated = 0;
        $skipped  = 0;
        $errors   = [];

        foreach ($fields as $row) {
            $v1Id  = $row['custom_field_id'] ?? null;
            $table = mb_trim((string) ($row['custom_field_table'] ?? ''));
            $label = mb_trim((string) ($row['custom_field_label'] ?? ''));

            if ($label === '') {
                $skipped++;
                continue;
            }

            if ($context->isDryRun()) {
                if ($v1Id !== null) {
                    $context->mapId('custom_fields', $v1Id, (int) $v1Id);
                }
                $migrated++;
                continue;
            }

            try {
                $fieldableType = match (mb_strtolower($table)) {
                    'ip_invoice_custom', 'invoices' => Invoice::class,
                    'ip_quote_custom', 'quotes'     => Quote::class,
                    default                         => Relation::class,
                };

                $customField = CustomField::withoutGlobalScopes()
                    ->where('company_id', $context->getCompanyId())
                    ->where('fieldable_type', $fieldableType)
                    ->where('custom_field_label', $label)
                    ->first();

                if ( ! $customField) {
                    $customField = CustomField::create([
                        'company_id'         => $context->getCompanyId(),
                        'fieldable_type'     => $fieldableType,
                        'custom_field_label' => $label,
                        'field_type'         => 'text',
                        'field_order'        => (int) ($row['custom_field_order'] ?? 1),
                    ]);
                    $context->recordCreated(CustomField::class, $customField->id);
                }

                if ($v1Id !== null) {
                    $context->mapId('custom_fields', $v1Id, $customField->id);
                }

                $migrated++;
            } catch (Throwable $e) {
                $errors[] = "Failed to migrate custom field '{$label}': " . $e->getMessage();
                $skipped++;
            }
        }

        $context->log("Migrated {$migrated} custom fields ({$skipped} skipped).");

        return [
            'migrated' => $migrated,
            'skipped'  => $skipped,
            'errors'   => $errors,
        ];
    }

    public function rollback(MigrationContext $context): int
    {
        $valIds   = $context->getCreatedIds(CustomFieldValue::class);
        $fieldIds = $context->getCreatedIds(CustomField::class);

        if ( ! empty($valIds)) {
            CustomFieldValue::withoutGlobalScopes()->whereIn('id', $valIds)->delete();
        }

        if (empty($fieldIds)) {
            return 0;
        }

        return CustomField::withoutGlobalScopes()
            ->whereIn('id', $fieldIds)
            ->where('company_id', $context->getCompanyId())
            ->delete();
    }
}
