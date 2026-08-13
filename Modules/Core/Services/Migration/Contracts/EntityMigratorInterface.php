<?php

namespace Modules\Core\Services\Migration\Contracts;

use Modules\Core\Services\Migration\MigrationContext;

interface EntityMigratorInterface
{
    /**
     * Name / identifier of this migrator (e.g. 'tax_rates', 'clients', 'invoices').
     */
    public function name(): string;

    /**
     * Human-readable label for UI / CLI progress reporting.
     */
    public function label(): string;

    /**
     * Inspect source data and count potential records and skipped/unmappable rows.
     *
     * @return array{source_count: int, will_migrate: int, unmappable: int, notes: array<string>}
     */
    public function inspect(MigrationContext $context): array;

    /**
     * Execute the migration for this entity.
     *
     * @return array{migrated: int, skipped: int, errors: array<string>}
     */
    public function migrate(MigrationContext $context): array;

    /**
     * Rollback records created by this migrator in the specified context batch.
     */
    public function rollback(MigrationContext $context): int;
}
