<?php

namespace Modules\Core\Services\Import;

use Illuminate\Support\Facades\DB;

abstract class AbstractImportService implements ImportServiceInterface
{
    protected const IMPORT_CONNECTION = 'import_v1';

    protected int $companyId;

    protected array $idMappings = [];

    protected array $stats = [];

    /**
     * Check if a table exists in the import database
     */
    protected function tableExists(string $tableName): bool
    {
        try {
            $tables = DB::connection(self::IMPORT_CONNECTION)
                ->select('SHOW TABLES');

            $tableKey = 'Tables_in_' . DB::connection(self::IMPORT_CONNECTION)->getDatabaseName();

            foreach ($tables as $table) {
                if (isset($table->$tableKey) && $table->$tableKey === $tableName) {
                    return true;
                }
            }

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get data from import database table
     */
    protected function getImportData(string $tableName): \Illuminate\Support\Collection
    {
        if (! $this->tableExists($tableName)) {
            return collect([]);
        }

        return DB::connection(self::IMPORT_CONNECTION)
            ->table($tableName)
            ->get();
    }

    /**
     * Initialize statistics array
     */
    protected function initStats(array $keys): void
    {
        foreach ($keys as $key) {
            $this->stats[$key] = 0;
        }
    }
}
