<?php

namespace Modules\Core\Services\Import;

use Modules\Projects\Models\Project;
use Modules\Projects\Models\Task;

class ProjectsImportService extends AbstractImportService
{
    public function getTables(): array
    {
        return ['ip_projects', 'ip_tasks'];
    }

    public function import(int $companyId, array &$idMappings): array
    {
        $this->companyId = $companyId;
        $this->idMappings = &$idMappings;
        $this->initStats(['projects', 'tasks']);

        $this->importProjects();
        $this->importTasks();

        return $this->stats;
    }

    private function importProjects(): void
    {
        $projects = $this->getImportData('ip_projects');

        foreach ($projects as $v1Project) {
            $clientId = $this->idMappings['clients'][$v1Project->client_id] ?? null;

            if (! $clientId) {
                continue;
            }

            $project = Project::create([
                'company_id'      => $this->companyId,
                'relation_id'     => $clientId,
                'project_name'    => $v1Project->project_name,
                'project_status'  => $v1Project->project_status ?? 'active',
                'project_description' => $v1Project->project_description ?? null,
            ]);

            $this->idMappings['projects'][$v1Project->project_id] = $project->id;
            $this->stats['projects']++;
        }
    }

    private function importTasks(): void
    {
        $tasks = $this->getImportData('ip_tasks');

        foreach ($tasks as $v1Task) {
            $projectId = $this->idMappings['projects'][$v1Task->project_id] ?? null;

            if (! $projectId) {
                continue;
            }

            Task::create([
                'company_id'      => $this->companyId,
                'project_id'      => $projectId,
                'task_name'       => $v1Task->task_name,
                'task_description' => $v1Task->task_description ?? null,
                'task_status'     => $v1Task->task_status ?? 'pending',
                'task_price'      => $v1Task->task_price ?? 0,
            ]);

            $this->stats['tasks']++;
        }
    }
}
