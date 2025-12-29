<?php

namespace Modules\Projects\Database\Factories;

use Modules\Core\Database\Factories\AbstractFactory;
use Modules\Projects\Enums\ProjectStatus;
use Modules\Projects\Models\Project;

class ProjectFactory extends AbstractFactory
{
    protected $model = Project::class;

    protected $company;

    public function definition(): array
    {
        $status    = $this->faker->randomElement(ProjectStatus::cases());
        $startDate = $this->faker->dateTimeBetween('-4 years', '+2 years');
        $endDate   = $startDate
            ? $this->faker->optional(0.7)->dateTimeBetween($startDate, '+2 years')
            : null;

        return [
            'project_number' => $this->faker->unique()->numerify('PRJ-#####'),
            'project_status' => $status->value,
            'project_name'   => $this->faker->sentence(),
            'start_at'       => $startDate?->format('Y-m-d'),
            'end_at'         => $endDate?->format('Y-m-d'),
            'description'    => null,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => [
            'project_status' => ProjectStatus::PLANNED->value,
        ]);
    }

    public function active(): static
    {
        return $this->state(fn () => [
            'project_status' => ProjectStatus::ACTIVE->value,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'project_status' => ProjectStatus::COMPLETED->value,
        ]);
    }
}
