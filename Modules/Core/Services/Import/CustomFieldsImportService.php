<?php

namespace Modules\Core\Services\Import;

use Modules\Core\Models\CustomField;
use Modules\Core\Models\CustomFieldValue;

class CustomFieldsImportService extends AbstractImportService
{
    public function getTables(): array
    {
        return ['ip_custom_fields', 'ip_custom_values'];
    }

    public function import(int $companyId, array &$idMappings): array
    {
        $this->companyId = $companyId;
        $this->idMappings = &$idMappings;
        $this->initStats(['custom_fields', 'custom_field_values']);

        $this->importCustomFields();
        $this->importCustomFieldValues();

        return $this->stats;
    }

    private function importCustomFields(): void
    {
        $fields = $this->getImportData('ip_custom_fields');

        foreach ($fields as $v1Field) {
            $customField = CustomField::create([
                'company_id'         => $this->companyId,
                'custom_field_table' => $v1Field->custom_field_table ?? 'invoices',
                'custom_field_label' => $v1Field->custom_field_label ?? 'Custom Field',
                'custom_field_column' => $v1Field->custom_field_column ?? null,
            ]);

            $this->idMappings['custom_fields'][$v1Field->custom_field_id] = $customField->id;
            $this->stats['custom_fields']++;
        }
    }

    private function importCustomFieldValues(): void
    {
        $values = $this->getImportData('ip_custom_values');

        foreach ($values as $v1Value) {
            $customFieldId = $this->idMappings['custom_fields'][$v1Value->custom_field_id] ?? null;

            if (! $customFieldId) {
                continue;
            }

            CustomFieldValue::create([
                'company_id'       => $this->companyId,
                'custom_field_id'  => $customFieldId,
                'model_id'         => $v1Value->entity_id ?? null,
                'model_type'       => $this->mapModelType($v1Value->entity_type ?? 'invoice'),
                'custom_field_value' => $v1Value->custom_field_value ?? '',
            ]);

            $this->stats['custom_field_values']++;
        }
    }

    private function mapModelType(string $entityType): string
    {
        return match ($entityType) {
            'invoice'  => 'Modules\\Invoices\\Models\\Invoice',
            'quote'    => 'Modules\\Quotes\\Models\\Quote',
            'client'   => 'Modules\\Clients\\Models\\Relation',
            'payment'  => 'Modules\\Payments\\Models\\Payment',
            default    => 'Modules\\Invoices\\Models\\Invoice',
        };
    }
}
