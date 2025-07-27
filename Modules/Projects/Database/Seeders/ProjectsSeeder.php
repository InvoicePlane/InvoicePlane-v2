<?php

namespace Modules\Projects\Database\Seeders;

use Modules\Clients\Database\Seeders\CustomersSeeder;
use Modules\Clients\Enums\RelationType;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\Company;
use Modules\Projects\Enums\ProjectStatus;
use Modules\Projects\Models\Project;
use RuntimeException;

class ProjectsSeeder extends \Modules\Core\Database\Seeders\AbstractSeeder
{
    protected array $projectNames = [
        'API Integration',
        'Brand Identity Design',
        'Cloud Infrastructure',
        'Content Management System',
        'Customer Portal',
        'Data Migration',
        'E-commerce Platform',
        'Marketing Campaign',
        'Mobile App Development',
        'Product Launch',
        'Security Audit',
        'SEO Optimization',
        'Social Media Strategy',
        'UI/UX Overhaul',
        'Website Redesign',
    ];

    protected array $projectDescriptions = [
        'Complete redesign of the existing website with modern UI/UX principles.',
        'Development of a cross-platform mobile application for iOS and Android.',
        'Implementation of an e-commerce platform with payment gateway integration.',
        'Comprehensive marketing campaign across multiple channels.',
        'End-to-end management of a new product launch.',
        'Creation of a cohesive brand identity including logo and style guide.',
        'Custom content management system tailored to specific business needs.',
        'Search engine optimization to improve organic search rankings.',
        'Development and execution of a social media marketing strategy.',
        'Customer self-service portal with account management features.',
        'Integration of third-party APIs to extend functionality.',
        'Migration of legacy data to new systems with validation.',
        'Setup and configuration of cloud infrastructure on AWS/Azure/GCP.',
        'Comprehensive security audit and vulnerability assessment.',
        'Complete overhaul of user interface and user experience.',
    ];

    public function run(?int $companyId = null): void
    {
        $query = Company::query();

        if ($companyId) {
            $query->where('id', $companyId);
        }

        $query->each(function (Company $company) {
            $this->command->info("Seeding projects for company: {$company->name}");

            // Get existing project count for progress tracking
            $existingCount = Project::query()->where('company_id', $company->id)->count();

            // Get customers for this company
            $customers = Relation::query()
                ->where('company_id', $company->id)
                ->where('relation_type', RelationType::CUSTOMER)
                ->get();

            // If no customers, create some
            if ($customers->isEmpty()) {
                $this->command->warn("No customers found for company {$company->name}. Creating some...");
                $this->call(CustomersSeeder::class, ['companyId' => $company->id]);
                $customers = Relation::query()
                    ->where('company_id', $company->id)
                    ->where('relation_type', RelationType::CUSTOMER)
                    ->get();

                // If still no customers after seeding, create a default one
                if ($customers->isEmpty()) {
                    $this->command->warn('Failed to create customers. Creating a default customer...');
                    $customer = Relation::factory()
                        ->for($company)
                        ->create([
                            'relation_type'   => RelationType::CUSTOMER,
                            'relation_status' => 'active',
                            'company_name'    => 'Default Customer',
                        ]);
                    $customers->push($customer);
                }
            }

            // Determine how many projects to create (5-15, but fewer if we already have some)
            $targetCount      = rand(5, 15);
            $projectsToCreate = max(0, $targetCount - $existingCount);

            if ($projectsToCreate <= 0) {
                $this->command->info("Company {$company->name} already has {$existingCount} projects. No new projects needed.");

                return;
            }

            $bar = $this->command->getOutput()->createProgressBar($projectsToCreate);
            $bar->start();

            for ($i = 0; $i < $projectsToCreate; $i++) {
                $this->createProject($company, $customers->random());
                $bar->advance();
            }

            $bar->finish();
            $this->command->newLine(2);
            $this->command->info("Created {$projectsToCreate} projects for company: {$company->name}");
        });
    }

    protected function createProject(Company $company, Relation $customer): void
    {
        // Verify the customer belongs to the same company
        if ($customer->company_id !== $company->id) {
            throw new RuntimeException("Customer {$customer->id} does not belong to company {$company->id}");
        }

        $statuses = [
            ProjectStatus::PLANNED,
            ProjectStatus::ACTIVE,
            ProjectStatus::ON_HOLD,
            ProjectStatus::COMPLETED,
            ProjectStatus::CANCELLED,
        ];

        $status    = $statuses[array_rand($statuses)];
        $startDate = now()->subDays(random_int(0, 180));
        $endDate   = $startDate->copy()->addDays(random_int(30, 365));

        $projectIndex = array_rand($this->projectNames);
        $name         = $this->projectNames[$projectIndex];
        $description  = $this->projectDescriptions[$projectIndex] ?? 'Project description not available.';

        // Check if a similar project already exists for this company
        $existingProject = Project::query()
            ->where('company_id', $company->id)
            ->where('project_name', $name)
            ->first();

        if ($existingProject) {
            // Update the existing project instead of creating a new one
            $existingProject->update([
                'customer_id'    => $customer->id,
                'project_status' => $status->value,
                'start_at'       => $startDate,
                'end_at'         => $endDate,
                'description'    => $description,
            ]);

            return;
        }

        // Create a new project
        Project::factory()
            ->for($company)
            ->for($customer, 'customer')
            ->create([
                'project_status' => $status->value,
                'project_name'   => $name,
                'start_at'       => $startDate,
                'end_at'         => $endDate,
                'description'    => $description,
            ]);
    }

    protected function calculateProgress(ProjectStatus $status): int
    {
        return match($status) {
            ProjectStatus::PLANNED   => 0,
            ProjectStatus::ACTIVE    => random_int(5, 90),
            ProjectStatus::ON_HOLD   => random_int(10, 80),
            ProjectStatus::COMPLETED => 100,
            ProjectStatus::CANCELLED => random_int(0, 100),
            default                  => 0,
        };
    }
}
