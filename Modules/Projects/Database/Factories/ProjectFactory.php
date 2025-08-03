<?php

namespace Modules\Projects\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Clients\Enums\RelationType;
use Modules\Clients\Models\Relation;
use Modules\Core\Database\Factories\AbstractFactory;
use Modules\Core\Models\Company;
use Modules\Projects\Enums\ProjectStatus;
use Modules\Projects\Models\Project;

/**
 * @extends Factory<\Modules\Projects\Models\Project>
 */
class ProjectFactory extends AbstractFactory
{
    protected $model = Project::class;

    protected $company;

    public function definition(): array
    {
        $companyId = $attributes['company_id'] ?? (Company::query()->inRandomOrder()->first()?->id ?? null);

        $customer = Relation::query()
            ->where('company_id', $companyId)
            ->where('relation_type', RelationType::CUSTOMER->value)
            ->inRandomOrder()
            ->first();

        if ( ! $customer) {
            $customer = Relation::factory()
                ->customer()
                ->state([
                    'company_id' => $companyId,
                ])
                ->create();
        }

        $status    = $this->faker->randomElement(ProjectStatus::cases());
        $startDate = $this->faker->optional()->dateTimeBetween('-4 years', '+2 years');
        $endDate   = $startDate
            ? $this->faker->optional(0.7)->dateTimeBetween($startDate, '+2 years')
            : null;

        return [
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
