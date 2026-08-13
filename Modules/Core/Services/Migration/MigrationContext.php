<?php

namespace Modules\Core\Services\Migration;

use Closure;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Collection;
use Modules\Core\Models\Company;
use Modules\Core\Models\User;

class MigrationContext
{
    protected Company $company;
    protected ?User $user = null;
    protected bool $dryRun = false;
    protected string $batchId;
    protected string $tablePrefix = 'ip_';

    /**
     * @var array<string, array<string|int, int>>
     */
    protected array $idMap = [];

    /**
     * @var array<string, array<int>>
     */
    protected array $createdRecords = [];

    /**
     * @var array<string>
     */
    protected array $logs = [];

    /**
     * @var array<string>
     */
    protected array $warnings = [];

    /**
     * @var array<string>
     */
    protected array $errors = [];

    /**
     * @var array<string, Collection<int, array<string, mixed>>>
     */
    protected array $sourceTables = [];

    protected ?ConnectionInterface $dbConnection = null;

    /**
     * @var ?Closure(string $status, string $message): void
     */
    protected ?Closure $progressCallback = null;

    public function __construct(
        Company $company,
        ?User $user = null,
        bool $dryRun = false,
        ?string $batchId = null,
        string $tablePrefix = 'ip_'
    ) {
        $this->company = $company;
        $this->user = $user;
        $this->dryRun = $dryRun;
        $this->batchId = $batchId ?? ('v1_mig_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)));
        $this->tablePrefix = $tablePrefix;
    }

    public function getCompany(): Company
    {
        return $this->company;
    }

    public function getCompanyId(): int
    {
        return $this->company->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function getUserId(): int
    {
        return $this->user?->id ?? 1;
    }

    public function isDryRun(): bool
    {
        return $this->dryRun;
    }

    public function setDryRun(bool $dryRun): self
    {
        $this->dryRun = $dryRun;
        return $this;
    }

    public function getBatchId(): string
    {
        return $this->batchId;
    }

    public function getTablePrefix(): string
    {
        return $this->tablePrefix;
    }

    public function setDbConnection(?ConnectionInterface $connection): self
    {
        $this->dbConnection = $connection;
        return $this;
    }

    public function getDbConnection(): ?ConnectionInterface
    {
        return $this->dbConnection;
    }

    /**
     * Set source tables in-memory (e.g. from SQL dump parser).
     *
     * @param array<string, Collection<int, array<string, mixed>>|array<array<string, mixed>>> $tables
     */
    public function setSourceTables(array $tables): self
    {
        $this->sourceTables = [];
        foreach ($tables as $name => $rows) {
            $this->sourceTables[$name] = $rows instanceof Collection ? $rows : collect($rows);
        }
        return $this;
    }

    /**
     * Get records from a source v1 table (without prefix or with prefix).
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function getSourceTable(string $table): Collection
    {
        $cleanName = str_starts_with($table, $this->tablePrefix)
            ? substr($table, strlen($this->tablePrefix))
            : $table;

        $fullName = $this->tablePrefix . $cleanName;

        // In-memory parsed tables
        if (isset($this->sourceTables[$fullName])) {
            return $this->sourceTables[$fullName];
        }

        if (isset($this->sourceTables[$cleanName])) {
            return $this->sourceTables[$cleanName];
        }

        // Direct DB connection
        if ($this->dbConnection) {
            try {
                $rows = $this->dbConnection->table($fullName)->get();
                return collect($rows)->map(fn ($r) => (array) $r);
            } catch (\Throwable $e) {
                // Try clean name if prefixed failed
                try {
                    $rows = $this->dbConnection->table($cleanName)->get();
                    return collect($rows)->map(fn ($r) => (array) $r);
                } catch (\Throwable) {
                    return collect();
                }
            }
        }

        return collect();
    }

    public function hasSourceTable(string $table): bool
    {
        $cleanName = str_starts_with($table, $this->tablePrefix)
            ? substr($table, strlen($this->tablePrefix))
            : $table;

        $fullName = $this->tablePrefix . $cleanName;

        if (isset($this->sourceTables[$fullName]) || isset($this->sourceTables[$cleanName])) {
            return true;
        }

        if ($this->dbConnection) {
            try {
                return $this->dbConnection->getSchemaBuilder()->hasTable($fullName)
                    || $this->dbConnection->getSchemaBuilder()->hasTable($cleanName);
            } catch (\Throwable) {
                return false;
            }
        }

        return false;
    }

    public function mapId(string $entity, int|string $v1Id, int $v2Id): void
    {
        $this->idMap[$entity][(string) $v1Id] = $v2Id;
    }

    public function getId(string $entity, int|string|null $v1Id): ?int
    {
        if ($v1Id === null || $v1Id === '') {
            return null;
        }
        return $this->idMap[$entity][(string) $v1Id] ?? null;
    }

    public function hasId(string $entity, int|string $v1Id): bool
    {
        return isset($this->idMap[$entity][(string) $v1Id]);
    }

    public function recordCreated(string $modelClass, int $id): void
    {
        $this->createdRecords[$modelClass][] = $id;
    }

    /**
     * @return array<int>
     */
    public function getCreatedIds(string $modelClass): array
    {
        return $this->createdRecords[$modelClass] ?? [];
    }

    /**
     * @return array<string, array<int>>
     */
    public function getAllCreatedRecords(): array
    {
        return $this->createdRecords;
    }

    public function log(string $message): void
    {
        $this->logs[] = '[' . date('H:i:s') . '] ' . $message;
        if ($this->progressCallback) {
            ($this->progressCallback)('info', $message);
        }
    }

    public function warn(string $warning): void
    {
        $this->warnings[] = $warning;
        if ($this->progressCallback) {
            ($this->progressCallback)('warning', $warning);
        }
    }

    public function error(string $error): void
    {
        $this->errors[] = $error;
        if ($this->progressCallback) {
            ($this->progressCallback)('error', $error);
        }
    }

    public function setProgressCallback(?Closure $callback): self
    {
        $this->progressCallback = $callback;
        return $this;
    }

    /**
     * @return array<string>
     */
    public function getLogs(): array
    {
        return $this->logs;
    }

    /**
     * @return array<string>
     */
    public function getWarnings(): array
    {
        return $this->warnings;
    }

    /**
     * @return array<string>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
