<?php

namespace Modules\Core\Services\Import;

use Modules\Core\Enums\ModelType;
use Modules\Core\Models\Note;

class NotesImportService extends AbstractImportService
{
    public function getTables(): array
    {
        return ['ip_notes'];
    }

    public function import(int $companyId, array &$idMappings): array
    {
        $this->companyId  = $companyId;
        $this->idMappings = &$idMappings;
        $this->initStats(['notes']);

        $this->importNotes();

        return $this->stats;
    }

    private function importNotes(): void
    {
        $notes = $this->getImportData('ip_notes');

        foreach ($notes as $v1Note) {
            $modelType = ModelType::fromString($v1Note->entity_type ?? 'invoice');
            $modelId   = $this->getModelId($modelType, $v1Note->entity_id ?? null);

            if ( ! $modelId) {
                continue;
            }

            Note::create([
                'company_id'   => $this->companyId,
                'notable_id'   => $modelId,
                'notable_type' => $modelType->value,
                'title'        => $v1Note->note_title ?? 'Note',
                'content'      => $v1Note->note ?? '',
            ]);

            $this->stats['notes']++;
        }
    }

    private function getModelId(ModelType $modelType, ?int $entityId): ?int
    {
        if ( ! $entityId) {
            return null;
        }

        return match ($modelType) {
            ModelType::INVOICE => $this->idMappings['invoices'][$entityId] ?? null,
            ModelType::QUOTE   => $this->idMappings['quotes'][$entityId] ?? null,
            ModelType::CLIENT  => $this->idMappings['clients'][$entityId] ?? null,
            default            => null,
        };
    }
}
