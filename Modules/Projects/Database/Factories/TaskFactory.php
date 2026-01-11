<?php

namespace Modules\Projects\Database\Factories;

use Modules\Clients\Models\Relation;
use Modules\Core\Database\Factories\AbstractFactory;
use Modules\Core\Models\Company;
use Modules\Projects\Enums\TaskStatus;
use Modules\Projects\Models\Task;

class TaskFactory extends AbstractFactory
{
    protected $model = Task::class;

    public function configure(): static
    {
        return $this->afterCreating(function (Task $task): void {
            if ($task->company_id === null || $task->customer_id !== null) {
                return;
            }

            $customerId = Relation::query()
                ->where('company_id', $task->company_id)
                ->where('relation_type', \Modules\Clients\Enums\RelationType::CUSTOMER->value)
                ->inRandomOrder()
                ->value('id');

            if ($customerId === null) {
                $company = Company::query()->find($task->company_id);
                if ($company === null) {
                    return;
                }

                $customer = Relation::factory()
                    ->for($company)
                    ->customer()
                    ->create();

                $customerId = $customer->id;
            }

            $task->customer_id = $customerId;
            $task->save();
        });
    }

    public function definition(): array
    {
        $companyId = $this->resolveCompanyId();
        if (!$companyId) {
            $company = Company::factory()->create();
            $companyId = $company->id;
        }
        // Get a customer for this company if one already exists
        $customerId = null;
        if ($companyId) {
            $customerId = Relation::query()
                ->where('company_id', $companyId)
                ->where('relation_type', \Modules\Clients\Enums\RelationType::CUSTOMER->value)
                ->inRandomOrder()
                ->value('id');
            if (!$customerId) {
                $customer = Relation::factory()
                    ->for(Company::query()->find($companyId))
                    ->customer()
                    ->create();
                $customerId = $customer->id;
            }
        }
        return [
            'company_id'  => $companyId,
            'customer_id' => $customerId,
            'task_number' => $this->faker->unique()->numerify('TSK-#####'),
            'assigned_to' => null,
            'task_status' => $this->faker->randomElement(TaskStatus::cases())->value,
            'task_name'   => $this->faker->words(3, true),
            'task_price'  => $this->faker->randomFloat(4, 0, 100),
            'due_at'      => $this->faker->dateTimeBetween('-3 year', '+2 year')->format('Y-m-d'),
            'description' => null,
        ];
    }
}
