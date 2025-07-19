<?php

namespace Modules\Projects\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Clients\Enums\RelationType;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\Company;
use Modules\Projects\Enums\ProjectStatus;
use Modules\Projects\Models\Project;

/**
 * @extends Factory<\Modules\Projects\Models\Project>
 */
class ProjectFactory extends Factory
{
    protected $model = Project::class;

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

        $status    = $this->faker->randomElement(ProjectStatus::cases());
        $startDate = $this->faker->optional()->dateTimeBetween('-4 years', '+2 years');
        $endDate   = $startDate
            ? $this->faker->optional()->dateTimeBetween($startDate, '+2 years')
            : null;

        return [
            'company_id'     => $company->id,
            'customer_id'    => $customer->id,
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
