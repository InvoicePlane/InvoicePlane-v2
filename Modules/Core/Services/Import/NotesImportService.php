<?php

namespace Modules\Core\Services\Import;

use Modules\Core\Models\Note;

class NotesImportService extends AbstractImportService
{
    public function getTables(): array
    {
        return ['ip_notes'];
    }

    public function import(int $companyId, array &$idMappings): array
    {
        $this->companyId = $companyId;
        $this->idMappings = &$idMappings;
        $this->initStats(['notes']);

        $this->importNotes();

        return $this->stats;
    }

    private function importNotes(): void
    {
        $notes = $this->getImportData('ip_notes');

        foreach ($notes as $v1Note) {
            $modelId = $this->getModelId($v1Note->entity_type ?? 'invoice', $v1Note->entity_id ?? null);

            if (! $modelId) {
                continue;
            }

            Note::create([
                'company_id'  => $this->companyId,
                'notable_id'  => $modelId,
                'notable_type' => $this->mapModelType($v1Note->entity_type ?? 'invoice'),
                'note'        => $v1Note->note ?? '',
            ]);

            $this->stats['notes']++;
        }
    }

    private function getModelId(string $entityType, ?int $entityId): ?int
    {
        if (! $entityId) {
            return null;
        }

        return match ($entityType) {
            'invoice'  => $this->idMappings['invoices'][$entityId] ?? null,
            'quote'    => $this->idMappings['quotes'][$entityId] ?? null,
            'client'   => $this->idMappings['clients'][$entityId] ?? null,
            'payment'  => null, // Payments don't have a direct mapping from v1
            default    => null,
        };
    }

    private function mapModelType(string $entityType): string
    {
        return match ($entityType) {
            'invoice'  => 'Modules\\Invoices\\Models\\Invoice',
            'quote'    => 'Modules\\Quotes\\Models\\Quote',
            'client'   => 'Modules\\Clients\\Models\\Relation',
            default    => 'Modules\\Invoices\\Models\\Invoice',
        };
    }
}
