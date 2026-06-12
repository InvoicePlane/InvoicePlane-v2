<?php

namespace Modules\Core\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Models\DocumentGroup;

class DocumentGroupRepository
{
    /**
     * Find a document group by company ID, type, and optionally by name.
     *
     * @param int         $companyId
     * @param string      $type
     * @param string|null $name
     *
     * @return DocumentGroup|null
     */
    public function findByCompanyAndType(
        int $companyId,
        string $type,
        ?string $name = null
    ): ?DocumentGroup {
        return DocumentGroup::query()
            ->where('type', $type)
            ->when($name, function (Builder $query) use ($name) {
                return $query->where('name', $name);
            })
            ->first();
    }
}
