<?php

namespace Modules\Projects\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Clients\Enums\RelationType;
use Modules\Core\Database\Factories\AbstractFactory;
use Modules\Core\Models\Company;
use Modules\Core\Models\TaxRate;
use Modules\Projects\Enums\ProjectStatus;
use Modules\Projects\Enums\TaskStatus;
use Modules\Projects\Models\Project;
use Modules\Projects\Models\Task;

/**
 * @extends Factory<\Modules\Projects\Models\Task>
 */
class TaskFactory extends AbstractFactory
{
    protected $model = Task::class;

    public function definition(): array
    {
        $companyId = $attributes['company_id'] ?? (Company::query()->inRandomOrder()->first()?->id ?? null);
        $customer  = $this->findOrCreateRelationOfType($companyId, RelationType::CUSTOMER);

        $project = $this->findOrCreateWithCompany(
            Project::class,
            [
                'company_id'  => $companyId,
                'customer_id' => $customer->id,
            ],
            [
                'project_name'   => 'Project for ' . $customer->name,
                'project_status' => ProjectStatus::ACTIVE->value,
                'start_at'       => now()->subDays(random_int(1, 30)),
                'end_at'         => now()->addDays(random_int(30, 90)),
            ]
        );

        $taxRate = TaxRate::query()
            ->where('company_id', $companyId)
            ->inRandomOrder()
            ->firstOrFail();

        return [
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
