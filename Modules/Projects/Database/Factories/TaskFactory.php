<?php

namespace Modules\Projects\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Clients\Enums\RelationType;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\Company;
use Modules\Core\Models\TaxRate;
use Modules\Core\Models\User;
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
            ->first()
            ?? Company::factory()->create();

        // Create or get a customer that belongs to this company
        $customer = Relation::query()
            ->where('company_id', $company->id)
            ->where('relation_type', RelationType::CUSTOMER->value)
            ->inRandomOrder()
            ->first()
            ?? Relation::factory()
                ->for($company)
                ->customer()
                ->create();

        // Create or get a project that belongs to this company and customer
        $project = Project::query()
            ->where('company_id', $company->id)
            ->where('customer_id', $customer->id)
            ->inRandomOrder()
            ->first()
            ?? Project::factory()
                ->for($company)
                ->for($customer)
                ->create();

        // Create or get a tax rate that belongs to this company
        $taxRate = TaxRate::query()
            ->where('company_id', $company->id)
            ->inRandomOrder()
            ->first()
            ?? TaxRate::factory()
                ->for($company)
                ->create();

        // Get a user that belongs to this company
        $user = User::query()
            ->whereHas('companies', fn ($q) => $q->where('companies.id', $company->id))
            ->inRandomOrder()
            ->first()
            ?? User::factory()
                ->hasAttached($company)
                ->create();

        return [
            'company_id'  => $company->id,
            'customer_id' => $customer->id,
            'project_id'  => $project->id,
            'tax_rate_id' => $taxRate->id,
            'assigned_to' => $this->faker->boolean(50) ? $user->id : null,
            'task_status' => $this->faker->randomElement(TaskStatus::cases())->value,
            'task_name'   => $this->faker->words(3, true),
            'task_price'  => $this->faker->randomFloat(4, 0, 100),
            'due_at'      => $this->faker->dateTimeBetween('-3 year', '+2 year')->format('Y-m-d'),
            'description' => null,
        ];
    }
}
