<?php

namespace Modules\Projects\Database\Factories;

use Modules\Core\Database\Factories\AbstractFactory;
use Modules\Projects\Enums\TaskStatus;
use Modules\Projects\Models\Task;

class TaskFactory extends AbstractFactory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
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
