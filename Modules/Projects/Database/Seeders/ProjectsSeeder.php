<?php

namespace Modules\Projects\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Clients\Database\Seeders\CustomersSeeder;
use Modules\Clients\Enums\RelationType;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\Company;
use Modules\Projects\Enums\ProjectStatus;
use Modules\Projects\Models\Project;

class ProjectsSeeder extends Seeder
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
            $existingCount = Project::query()->where('company_id', $company->id)->count();

            if ($existingCount > 0) {
                $this->command->info("Skipping projects for company {$company->name} - already has {$existingCount} projects.");

                return;
            }

            $this->command->info("Creating projects for company: {$company->name}");

            $customers = Relation::query()->where('company_id', $company->id)
                ->where('relation_type', RelationType::CUSTOMER)
                ->get();

            if ($customers->isEmpty()) {
                $this->command->warn("No customers found for company {$company->name}. Creating some...");
                $this->call(CustomersSeeder::class, ['companyId' => $company->id]);
                $customers = Relation::query()->where('company_id', $company->id)
                    ->where('relation_type', RelationType::CUSTOMER)
                    ->get();
            }

            $projectCount = rand(5, 15);

            for ($i = 0; $i < $projectCount; $i++) {
                $this->createProject($company, $customers->random());
            }

            $this->command->info("Created {$projectCount} projects for company: {$company->name}");
        });
    }

    protected function createProject(Company $company, Relation $customer): void
    {
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

        Project::factory()
            ->for($company)
            ->for($customer, 'customer')
            ->create([
                'project_name'   => $name,
                'description'    => $description,
                'project_status' => $status->value,
                'start_at'       => $startDate,
                'end_at'         => $endDate,
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
