<?php

namespace Modules\Core\Services\Migration;

use Closure;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Modules\Core\Models\Company;
use Modules\Core\Models\User;
use Modules\Core\Services\BaseService;
use Modules\Core\Services\Migration\Contracts\EntityMigratorInterface;
use Modules\Core\Services\Migration\Migrators\ClientMigrator;
use Modules\Core\Services\Migration\Migrators\CustomFieldMigrator;
use Modules\Core\Services\Migration\Migrators\InvoiceMigrator;
use Modules\Core\Services\Migration\Migrators\PaymentMigrator;
use Modules\Core\Services\Migration\Migrators\ProductMigrator;
use Modules\Core\Services\Migration\Migrators\ProjectMigrator;
use Modules\Core\Services\Migration\Migrators\QuoteMigrator;
use Modules\Core\Services\Migration\Migrators\TaxRateMigrator;
use Modules\Core\Services\Migration\Support\FinancialInvariantValidator;
use Modules\Core\Services\Migration\Support\V1SqlDumpParser;
use Throwable;

class V1MigrationManager extends BaseService
{
    /**
     * @var array<EntityMigratorInterface>
     */
    protected array $migrators = [];

    protected V1SqlDumpParser $sqlParser;

    protected FinancialInvariantValidator $invariantValidator;

    public function __construct(\Illuminate\Container\Container $app)
    {
        parent::__construct($app);
        $this->sqlParser          = new V1SqlDumpParser();
        $this->invariantValidator = new FinancialInvariantValidator();
        $this->registerDefaultMigrators();
    }

    public function model(): string
    {
        return Company::class;
    }

    /**
     * @return array<EntityMigratorInterface>
     */
    public function getMigrators(): array
    {
        return $this->migrators;
    }

    /**
     * Build a MigrationContext from an SQL dump file or content.
     */
    public function createContextFromSql(
        string $sqlOrPath,
        Company $company,
        ?User $user = null,
        bool $dryRun = false,
        string $tablePrefix = 'ip_'
    ): MigrationContext {
        $context = new MigrationContext($company, $user, $dryRun, null, $tablePrefix);
        $tables  = $this->sqlParser->parse($sqlOrPath);
        $context->setSourceTables($tables);

        return $context;
    }

    /**
     * Build a MigrationContext from a database connection name or dynamic config.
     *
     * @param string|array<string, mixed> $connectionOrConfig
     */
    public function createContextFromDb(
        string|array $connectionOrConfig,
        Company $company,
        ?User $user = null,
        bool $dryRun = false,
        string $tablePrefix = 'ip_'
    ): MigrationContext {
        $context = new MigrationContext($company, $user, $dryRun, null, $tablePrefix);

        if (is_array($connectionOrConfig)) {
            $tempConnName = 'v1_import_dynamic_' . uniqid();
            Config::set("database.connections.{$tempConnName}", [
                'driver'         => 'mysql',
                'host'           => $connectionOrConfig['host'] ?? '127.0.0.1',
                'port'           => $connectionOrConfig['port'] ?? '3306',
                'database'       => $connectionOrConfig['database'] ?? '',
                'username'       => $connectionOrConfig['username'] ?? 'root',
                'password'       => $connectionOrConfig['password'] ?? '',
                'charset'        => 'utf8mb4',
                'collation'      => 'utf8mb4_unicode_ci',
                'prefix'         => '',
                'prefix_indexes' => true,
                'strict'         => false,
            ]);
            $connection = DB::connection($tempConnName);
        } else {
            $connection = DB::connection($connectionOrConfig);
        }

        $context->setDbConnection($connection);

        return $context;
    }

    /**
     * Test connection credentials to a v1 MySQL database.
     *
     * @param array<string, mixed> $config
     *
     * @return array{success: bool, message: string, tables_found: int}
     */
    public function testDbConnection(array $config, string $prefix = 'ip_'): array
    {
        $tempConnName = 'v1_test_conn_' . uniqid();
        Config::set("database.connections.{$tempConnName}", [
            'driver'   => 'mysql',
            'host'     => $config['host'] ?? '127.0.0.1',
            'port'     => $config['port'] ?? '3306',
            'database' => $config['database'] ?? '',
            'username' => $config['username'] ?? 'root',
            'password' => $config['password'] ?? '',
            'charset'  => 'utf8mb4',
            'strict'   => false,
        ]);

        try {
            $pdo         = DB::connection($tempConnName)->getPdo();
            $tables      = DB::connection($tempConnName)->select('SHOW TABLES');
            $tablesFound = 0;
            foreach ($tables as $t) {
                $tableName = array_values((array) $t)[0] ?? '';
                if ($prefix === '' || str_starts_with($tableName, $prefix)) {
                    $tablesFound++;
                }
            }

            return [
                'success'      => true,
                'message'      => "Connected successfully. Found {$tablesFound} matching tables with prefix '{$prefix}'.",
                'tables_found' => $tablesFound,
            ];
        } catch (Throwable $e) {
            return [
                'success'      => false,
                'message'      => 'Connection failed: ' . $e->getMessage(),
                'tables_found' => 0,
            ];
        }
    }

    /**
     * Inspect source data across all entities (Dry Run Analysis).
     *
     * @return array{
     *     total_source_count: int,
     *     total_will_migrate: int,
     *     total_unmappable: int,
     *     entities: array<string, array{label: string, source_count: int, will_migrate: int, unmappable: int, notes: array<string>}>,
     *     warnings: array<string>
     * }
     */
    public function inspect(MigrationContext $context): array
    {
        $entities         = [];
        $totalSource      = 0;
        $totalWillMigrate = 0;
        $totalUnmappable  = 0;

        foreach ($this->migrators as $migrator) {
            $inspection                  = $migrator->inspect($context);
            $entities[$migrator->name()] = [
                'label'        => $migrator->label(),
                'source_count' => $inspection['source_count'],
                'will_migrate' => $inspection['will_migrate'],
                'unmappable'   => $inspection['unmappable'],
                'notes'        => $inspection['notes'],
            ];

            $totalSource += $inspection['source_count'];
            $totalWillMigrate += $inspection['will_migrate'];
            $totalUnmappable += $inspection['unmappable'];
        }

        return [
            'total_source_count' => $totalSource,
            'total_will_migrate' => $totalWillMigrate,
            'total_unmappable'   => $totalUnmappable,
            'entities'           => $entities,
            'warnings'           => $context->getWarnings(),
        ];
    }

    /**
     * Run the complete migration (or dry run).
     *
     * @param ?Closure(string $status, string $message): void $progressCallback
     *
     * @return array{
     *     success: bool,
     *     is_dry_run: bool,
     *     batch_id: string,
     *     results: array<string, array{label: string, migrated: int, skipped: int, errors: array<string>}>,
     *     financial_invariants: array{passed: bool, invoices_checked: int, quotes_checked: int, passed_count: int, failed_count: int, mismatches: array},
     *     logs: array<string>,
     *     warnings: array<string>,
     *     errors: array<string>
     * }
     */
    public function run(MigrationContext $context, ?Closure $progressCallback = null): array
    {
        if ($progressCallback) {
            $context->setProgressCallback($progressCallback);
        }

        // Auto scope Filament tenant if panel active
        if (class_exists(Filament::class)) {
            try {
                Filament::setTenant($context->getCompany());
            } catch (Throwable) {
                // Ignore if panel tenant not configured in CLI
            }
        }

        $isDryRun = $context->isDryRun();
        $context->log($isDryRun ? 'Starting migration DRY RUN...' : 'Starting migration execution...');

        $results = [];

        $executeLogic = function () use ($context, &$results) {
            foreach ($this->migrators as $migrator) {
                $context->log("Migrating {$migrator->label()}...");
                $res                        = $migrator->migrate($context);
                $results[$migrator->name()] = [
                    'label'    => $migrator->label(),
                    'migrated' => $res['migrated'],
                    'skipped'  => $res['skipped'],
                    'errors'   => $res['errors'],
                ];

                if ( ! empty($res['errors'])) {
                    foreach ($res['errors'] as $err) {
                        $context->error($err);
                    }
                }
            }
        };

        if ($isDryRun) {
            $executeLogic();
        } else {
            DB::transaction($executeLogic);
        }

        // Validate financial invariants if not dry run
        $invariants = $isDryRun
            ? ['passed' => true, 'invoices_checked' => 0, 'quotes_checked' => 0, 'passed_count' => 0, 'failed_count' => 0, 'mismatches' => []]
            : $this->invariantValidator->validate($context);

        if ( ! $invariants['passed']) {
            $context->warn("Financial invariants validation found {$invariants['failed_count']} mismatches.");
        } else {
            $context->log("Financial invariants verified successfully for all {$invariants['invoices_checked']} invoices and {$invariants['quotes_checked']} quotes.");
        }

        $context->log('Migration ' . ($isDryRun ? 'dry run ' : '') . 'completed successfully.');

        return [
            'success'              => empty($context->getErrors()),
            'is_dry_run'           => $isDryRun,
            'batch_id'             => $context->getBatchId(),
            'results'              => $results,
            'financial_invariants' => $invariants,
            'logs'                 => $context->getLogs(),
            'warnings'             => $context->getWarnings(),
            'errors'               => $context->getErrors(),
        ];
    }

    /**
     * Rollback a previously executed migration batch.
     */
    public function rollback(MigrationContext $context): array
    {
        $context->log("Rolling back batch {$context->getBatchId()}...");
        $deletedCounts = [];

        // Rollback in reverse dependency order
        $reverseMigrators = array_reverse($this->migrators);
        foreach ($reverseMigrators as $migrator) {
            $count                            = $migrator->rollback($context);
            $deletedCounts[$migrator->name()] = $count;
            $context->log("Rolled back {$count} {$migrator->label()} records.");
        }

        return [
            'success'        => true,
            'batch_id'       => $context->getBatchId(),
            'deleted_counts' => $deletedCounts,
            'logs'           => $context->getLogs(),
        ];
    }

    protected function registerDefaultMigrators(): void
    {
        $this->migrators = [
            new TaxRateMigrator(),
            new ClientMigrator(),
            new ProductMigrator(),
            new CustomFieldMigrator(),
            new InvoiceMigrator(),
            new PaymentMigrator(),
            new QuoteMigrator(),
            new ProjectMigrator(),
        ];
    }
}
