<?php

namespace Modules\Invoices\Services;

use Modules\Core\Models\DocumentGroup;
use Modules\Core\Services\BaseService;

class DocumentGroupService extends BaseService
{
    public function model(): string
    {
        return DocumentGroup::class;
    }
}
