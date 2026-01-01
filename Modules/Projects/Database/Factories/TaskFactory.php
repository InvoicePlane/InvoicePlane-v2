<?php

namespace Modules\Projects\Database\Factories;

use Modules\Core\Database\Factories\AbstractFactory;
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

            $customerId = \Modules\Clients\Models\Relation::query()
                ->where('company_id', $task->company_id)
                ->where('relation_type', \Modules\Clients\Enums\RelationType::CUSTOMER->value)
                ->inRandomOrder()
                ->value('id');

            if ($customerId === null) {
                $company = \Modules\Core\Models\Company::find($task->company_id);
                if ($company === null) {
                    return;
                }

                $customer = \Modules\Clients\Models\Relation::factory()
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

        // Get a customer for this company if one already exists
        $customerId = null;
        if ($companyId) {
            $customerId = \Modules\Clients\Models\Relation::query()
                ->where('company_id', $companyId)
                ->where('relation_type', \Modules\Clients\Enums\RelationType::CUSTOMER->value)
                ->inRandomOrder()
                ->value('id');
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
