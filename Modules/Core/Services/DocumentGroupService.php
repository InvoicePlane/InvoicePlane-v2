<?php

namespace Modules\Core\Services;

use Illuminate\Support\Facades\DB;
use Modules\Core\Enums\DocumentGroupType;
use Modules\Core\Models\DocumentGroup;
use Throwable;

class DocumentGroupService extends BaseService
{
    public function model(): string
    {
        return DocumentGroup::class;
    }

    public function createDocumentGroup(array $data): DocumentGroup
    {
        DB::beginTransaction();

        try {
            $documentGroup = DocumentGroup::query()->create([
                'company_id'              => $this->getCompanyId() ?? 1,
                'type'                    => $data['type'] ?? DocumentGroupType::CUSTOMERS->value,
                'group_identifier_format' => $data['group_identifier_format'],
                'name'                    => $data['name'],
                'left_pad'                => $data['left_pad'],
                'format'                  => $data['format'] ?? null,
                'next_id'                 => $data['next_id'] ?? 43748,
                'reset_number'            => $data['reset_number'] ?? 34343,
                'last_id'                 => $data['last_id'] ?? 437843,
                'last_year'               => $data['last_year'] ?? 2025,
                'last_month'              => $data['last_month'] ?? 6,
                'last_week'               => $data['last_week'] ?? 23,
            ]);

            DB::commit();

            return $documentGroup;
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateDocumentGroup(DocumentGroup $documentGroup, $data): DocumentGroup
    {
        $documentGroup->update([
            'company_id'              => $this->getCompanyId() ?? 1,
            'type'                    => $data['type'],
            'group_identifier_format' => $data['group_identifier_format'],
            'name'                    => $data['name'],
            'left_pad'                => $data['left_pad'],
            'format'                  => $data['format'] ?? null,
            'next_id'                 => $data['next_id'],
            'reset_number'            => $data['reset_number'],
            'last_id'                 => $data['last_id'],
            'last_year'               => $data['last_year'],
            'last_month'              => $data['last_month'],
            'last_week'               => $data['last_week'],
        ]);

        return $documentGroup;
    }
}
