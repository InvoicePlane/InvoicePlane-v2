<?php

namespace Modules\Projects\Database\Factories;

use Exception;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Clients\Enums\RelationType;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\Company;
use Modules\Core\Models\TaxRate;
use Modules\Projects\Enums\TaskStatus;
use Modules\Projects\Models\Project;
use Modules\Projects\Models\Task;

/**
 * @extends Factory<\Modules\Projects\Models\Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        $company = Company::query()
            ->inRandomOrder()
            ->firstOrFail();

        // Get a customer that belongs to this company
        $customer = Relation::query()
            ->where('company_id', $company->id)
            ->where('relation_type', RelationType::CUSTOMER->value)
            ->inRandomOrder()
            ->firstOrFail();

        // Get or create a project for this company and customer
        $project = Project::query()
            ->where('company_id', $company->id)
            ->where('customer_id', $customer->id)
            ->inRandomOrder()
            ->first();

        if (!$project) {
            // If no project exists, create one
            $project = Project::factory()->create([
                'company_id' => $company->id,
                'customer_id' => $customer->id,
                'name' => 'Project for ' . $customer->name,
                'status' => 'in_progress',
                'start_date' => now()->subDays(rand(1, 30)),
                'deadline' => now()->addDays(rand(30, 90)),
            ]);
        }

        // Get a tax rate that belongs to this company
        $taxRate = TaxRate::query()
            ->where('company_id', $company->id)
            ->inRandomOrder()
            ->firstOrFail();

        return [
            'company_id'  => $company->id,
            'customer_id' => $customer->id,
            'project_id'  => $project->id,
            'tax_rate_id' => $taxRate->id,
            'assigned_to' => null,
            'task_status' => $this->faker->randomElement(TaskStatus::cases())->value,
            'task_name'   => $this->faker->words(3, true),
            'task_price'  => $this->faker->randomFloat(4, 0, 100),
            'due_at'      => $this->faker->dateTimeBetween('-3 year', '+2 year')->format('Y-m-d'),
            'description' => null,
        ];
    }
}
