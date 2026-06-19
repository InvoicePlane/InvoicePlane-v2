<?php

namespace Modules\Projects\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\TaxRate;
use Modules\Projects\Enums\TaskStatus;
use Modules\Projects\Models\Project;
use Modules\Projects\Models\Task;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'project_id'       => Project::all()->random()->project_id,
            'task_name'        => $this->faker->word(),
            'task_description' => $this->faker->sentence(10),
            'task_price'       => $this->faker->randomFloat(2, 0, 100),
            'task_finish_date' => $this->faker->dateTimeBetween('-3 years', '+2 years'),
            'task_status'      => fn () => collect(TaskStatus::cases())->random()->value,
            'tax_rate_id'      => TaxRate::all()->random()->tax_rate_id,
        ];
    }
}
