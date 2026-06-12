<?php

namespace Modules\Core\Services\Import;

interface ImportServiceInterface
{
    /**
     * Import data from v1 to v2.
     *
     * @param int   $companyId  The company ID to import into
     * @param array $idMappings Reference to shared ID mappings array
     *
     * @return array Statistics about imported records
     */
    public function import(int $companyId, array &$idMappings): array;

    /**
     * Get the list of tables this service imports.
     *
     * @return array
     */
    public function getTables(): array;
}
