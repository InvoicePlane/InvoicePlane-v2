<?php

namespace Modules\Core\Services\Migration\Migrators;

use Modules\Core\Services\Migration\Contracts\EntityMigratorInterface;
use Modules\Core\Services\Migration\MigrationContext;
use Modules\Projects\Enums\ProjectStatus;
use Modules\Projects\Enums\TaskStatus;
use Modules\Projects\Models\Project;
use Modules\Projects\Models\Task;
use Throwable;

class ProjectMigrator implements EntityMigratorInterface
{
    public function name(): string
    {
        return 'projects';
    }

    public function label(): string
    {
        return 'Projects & Tasks';
    }

    public function inspect(MigrationContext $context): array
    {
        $projects    = $context->getSourceTable('projects');
        $tasks       = $context->getSourceTable('tasks');
        $notes       = [];
        $willMigrate = 0;
        $unmappable  = 0;

        foreach ($projects as $row) {
            $name = mb_trim((string) ($row['project_name'] ?? ''));
            if ($name === '') {
                $unmappable++;
                $notes[] = "Project row #{$row['project_id']} has empty name, will be skipped.";
            } else {
                $willMigrate++;
            }
        }

        return [
            'source_count' => $projects->count() + $tasks->count(),
            'will_migrate' => $willMigrate + $tasks->count(),
            'unmappable'   => $unmappable,
            'notes'        => $notes,
        ];
    }

    public function migrate(MigrationContext $context): array
    {
        $projects = $context->getSourceTable('projects');
        $tasks    = $context->getSourceTable('tasks')->groupBy('project_id');

        $migrated = 0;
        $skipped  = 0;
        $errors   = [];

        foreach ($projects as $row) {
            $v1Id       = $row['project_id'] ?? null;
            $name       = mb_trim((string) ($row['project_name'] ?? ''));
            $v1ClientId = $row['client_id'] ?? null;

            if ($name === '') {
                $skipped++;
                continue;
            }

            $customerId = $context->getId('clients', $v1ClientId);
            if ( ! $customerId) {
                // If client not mapped, find first client in company
                $firstClient = $context->getCompany()->relations()->first();
                $customerId  = $firstClient?->id;
            }

            if ( ! $customerId) {
                $errors[] = "Project #{$v1Id} '{$name}' skipped: no customer available.";
                $skipped++;
                continue;
            }

            if ($context->isDryRun()) {
                if ($v1Id !== null) {
                    $context->mapId('projects', $v1Id, (int) $v1Id);
                }
                $migrated++;
                continue;
            }

            try {
                $project = Project::withoutGlobalScopes()
                    ->where('company_id', $context->getCompanyId())
                    ->where('project_name', $name)
                    ->first();

                if ( ! $project) {
                    $project = Project::create([
                        'company_id'     => $context->getCompanyId(),
                        'customer_id'    => $customerId,
                        'project_name'   => $name,
                        'project_number' => 'PRJ-' . mb_str_pad((string) ($v1Id ?? rand(100, 9999)), 5, '0', STR_PAD_LEFT),
                        'project_status' => ProjectStatus::ACTIVE,
                    ]);
                    $context->recordCreated(Project::class, $project->id);
                }

                if ($v1Id !== null) {
                    $context->mapId('projects', $v1Id, $project->id);
                }

                // Migrate tasks under project
                $projectTasks = $tasks[$v1Id] ?? collect();
                foreach ($projectTasks as $taskRow) {
                    $v1TaskId  = $taskRow['task_id'] ?? null;
                    $taskName  = mb_trim((string) ($taskRow['task_name'] ?? 'Task'));
                    $status    = $this->resolveTaskStatus($taskRow);
                    $taxRateId = $context->getId('tax_rates', $taskRow['tax_rate_id'] ?? null);

                    $task = Task::create([
                        'company_id'  => $context->getCompanyId(),
                        'customer_id' => $customerId,
                        'project_id'  => $project->id,
                        'tax_rate_id' => $taxRateId,
                        'task_name'   => $taskName,
                        'description' => ! empty($taskRow['task_description']) ? (string) $taskRow['task_description'] : null,
                        'task_price'  => (float) ($taskRow['task_price'] ?? 0.0),
                        'task_status' => $status,
                        'due_at'      => ! empty($taskRow['task_finish_date']) ? $taskRow['task_finish_date'] : null,
                    ]);
                    $context->recordCreated(Task::class, $task->id);
                    if ($v1TaskId !== null) {
                        $context->mapId('tasks', $v1TaskId, $task->id);
                    }
                }

                $migrated++;
            } catch (Throwable $e) {
                $errors[] = "Failed to migrate project #{$v1Id} '{$name}': " . $e->getMessage();
                $skipped++;
            }
        }

        // Also handle orphan tasks (tasks without project_id)
        $orphanTasks = $tasks[''] ?? $tasks[0] ?? collect();
        foreach ($orphanTasks as $taskRow) {
            if ($context->isDryRun()) {
                continue;
            }

            try {
                $v1TaskId    = $taskRow['task_id'] ?? null;
                $taskName    = mb_trim((string) ($taskRow['task_name'] ?? 'Task'));
                $status      = $this->resolveTaskStatus($taskRow);
                $taxRateId   = $context->getId('tax_rates', $taskRow['tax_rate_id'] ?? null);
                $firstClient = $context->getCompany()->relations()->first();

                if ($firstClient) {
                    $task = Task::create([
                        'company_id'  => $context->getCompanyId(),
                        'customer_id' => $firstClient->id,
                        'project_id'  => null,
                        'tax_rate_id' => $taxRateId,
                        'task_name'   => $taskName,
                        'description' => ! empty($taskRow['task_description']) ? (string) $taskRow['task_description'] : null,
                        'task_price'  => (float) ($taskRow['task_price'] ?? 0.0),
                        'task_status' => $status,
                        'due_at'      => ! empty($taskRow['task_finish_date']) ? $taskRow['task_finish_date'] : null,
                    ]);
                    $context->recordCreated(Task::class, $task->id);
                    if ($v1TaskId !== null) {
                        $context->mapId('tasks', $v1TaskId, $task->id);
                    }
                }
            } catch (Throwable $e) {
                $errors[] = 'Failed to migrate task: ' . $e->getMessage();
            }
        }

        $context->log("Migrated {$migrated} projects ({$skipped} skipped).");

        return [
            'migrated' => $migrated,
            'skipped'  => $skipped,
            'errors'   => $errors,
        ];
    }

    public function rollback(MigrationContext $context): int
    {
        $taskIds    = $context->getCreatedIds(Task::class);
        $projectIds = $context->getCreatedIds(Project::class);

        if ( ! empty($taskIds)) {
            Task::withoutGlobalScopes()->whereIn('id', $taskIds)->delete();
        }

        if (empty($projectIds)) {
            return 0;
        }

        return Project::withoutGlobalScopes()
            ->whereIn('id', $projectIds)
            ->where('company_id', $context->getCompanyId())
            ->delete();
    }

    protected function resolveTaskStatus(array $row): TaskStatus
    {
        $status = (int) ($row['task_status'] ?? 1);

        return match ($status) {
            2       => TaskStatus::IN_PROGRESS,
            3       => TaskStatus::COMPLETED,
            4       => TaskStatus::PAID,
            default => TaskStatus::NOT_STARTED,
        };
    }
}
