<?php

namespace Modules\Projects\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Enums\UserRole;
use Modules\Core\Models\Company;
use Modules\Core\Models\User;
use Modules\Projects\Enums\ProjectStatus;
use Modules\Projects\Enums\TaskStatus;
use Modules\Projects\Models\Project;
use Modules\Projects\Models\Task;

class TasksSeeder extends Seeder
{
    protected array $taskTitles = [
        'Initial Planning',
        'Requirements Gathering',
        'Design Mockups',
        'Database Design',
        'API Development',
        'Frontend Development',
        'Backend Development',
        'Unit Testing',
        'Integration Testing',
        'User Acceptance Testing',
        'Performance Optimization',
        'Security Audit',
        'Documentation',
        'Deployment',
        'Post-Launch Review',
        'Bug Fixes',
        'Client Feedback Implementation',
        'Final Review',
        'Training',
        'Project Handover',
    ];

    protected array $taskDescriptions = [
        'Define project scope, objectives, and deliverables.',
        'Gather and document all requirements from stakeholders.',
        'Create design mockups for client approval.',
        'Design and implement the database schema.',
        'Develop the necessary API endpoints.',
        'Implement the user interface based on designs.',
        'Develop server-side logic and functionality.',
        'Write and execute unit tests.',
        'Test integration between different modules.',
        'Conduct user acceptance testing with stakeholders.',
        'Optimize application performance.',
        'Perform security audit and address vulnerabilities.',
        'Create technical and user documentation.',
        'Deploy the application to production environment.',
        'Review project outcomes and lessons learned.',
        'Address and fix reported bugs.',
        'Implement feedback received from the client.',
        'Conduct final review before launch.',
        'Provide training to end users.',
        'Complete project handover to the client.',
    ];

    public function run(?int $companyId = null): void
    {
        $query = Company::query();

        if ($companyId) {
            $query->where('id', $companyId);
        }

        $query->each(function (Company $company) {
            $existingCount = Task::query()->where('company_id', $company->id)->count();

            if ($existingCount > 0) {
                $this->command->info("Skipping tasks for company {$company->name} - already has {$existingCount} tasks.");

                return;
            }

            $this->command->info("Creating tasks for company: {$company->name}");

            $projects = Project::query()->where('company_id', $company->id)->get();

            if ($projects->isEmpty()) {
                $this->command->warn("No projects found for company {$company->name}. Creating some...");
                $this->call(ProjectsSeeder::class, ['companyId' => $company->id]);
                $projects = Project::query()->where('company_id', $company->id)->get();
            }

            foreach ($projects as $project) {
                $this->createProjectTasks($project);
            }

            $totalTasks = Task::query()->where('company_id', $company->id)->count();
            $this->command->info("Created {$totalTasks} tasks for company: {$company->name}");
        });
    }

    protected function createProjectTasks(Project $project): void
    {
        $taskCount   = rand(5, 15);
        $taskIndices = array_rand($this->taskTitles, min($taskCount, count($this->taskTitles)));

        if ( ! is_array($taskIndices)) {
            $taskIndices = [$taskIndices];
        }

        $users = $project->company->users()
            ->whereDoesntHave('roles', function ($query) {
                $query->whereIn('name', UserRole::elevated());
            })
            ->get();

        if ($users->isEmpty()) {
            $this->command->warn("No non-elevated users found for company {$project->company->name}. Tasks will be unassigned.");
        }

        $startDate = $project->start_at;
        $endDate   = $project->end_at;

        if ( ! $startDate || ! $endDate) {
            $this->command->warn("Project {$project->project_name} is missing start or end dates. Using default date range.");
            $startDate = now();
            $endDate   = now()->addDays(30);
        }

        $daysDiff = $startDate->diffInDays($endDate);

        foreach ($taskIndices as $index) {
            $title       = $this->taskTitles[$index];
            $description = $this->taskDescriptions[$index] ?? '';
            $status      = $this->getTaskStatusBasedOnProject($project->project_status);

            $taskStartDate = $startDate->copy()->addDays(random_int(0, (int) ($daysDiff * 0.7)));
            $taskEndDate   = $taskStartDate->copy()->addDays(random_int(1, max(1, (int) ($daysDiff * 0.3))));

            if ($taskEndDate > $endDate) {
                $taskEndDate = $endDate;
            }

            $assignToUser = $users->isNotEmpty() && random_int(1, 10) <= 7;
            $assignedToId = $assignToUser ? $users->random()->id : null;

            Task::factory()
                ->for($project->company)
                ->for($project)
                ->when($assignedToId, function ($factory) use ($assignedToId) {
                    return $factory->for(User::query()->where('id', $assignedToId)->first(), 'assignedTo');
                })
                ->create([
                    'task_status' => $status->value,
                    'task_name'   => $title,
                    'due_at'      => $taskEndDate,
                    'description' => $description,
                ]);
        }
    }

    protected function getTaskStatusBasedOnProject(ProjectStatus $projectStatus): TaskStatus
    {
        $statusMap = [
            ProjectStatus::PLANNED->value => [
                TaskStatus::NOT_STARTED,
            ],
            ProjectStatus::ACTIVE->value => [
                TaskStatus::IN_PROGRESS,
            ],
            ProjectStatus::COMPLETED->value => [
                TaskStatus::COMPLETED,
            ],
            ProjectStatus::ON_HOLD->value => [
                TaskStatus::NOT_STARTED,
            ],
            ProjectStatus::CANCELLED->value => [
                TaskStatus::CANCELLED,
            ],
        ];

        $statusValue      = $projectStatus->value;
        $possibleStatuses = $statusMap[$statusValue] ?? [TaskStatus::NOT_STARTED];

        return $possibleStatuses[array_rand($possibleStatuses)];
    }
}
