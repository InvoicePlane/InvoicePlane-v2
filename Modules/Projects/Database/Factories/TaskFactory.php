<?php

namespace Modules\Projects\Database\Factories;

use Modules\Clients\Enums\RelationType;

use Modules\Projects\Models\Task;

use Modules\Projects\Enums\TaskStatus;

use Modules\Projects\Database\Factories\TaskFactory;

use Modules\Core\Models\TaxRate;

use Modules\Core\Models\User;

use Modules\Core\Support\Results\Clients;

use Modules\Projects\Models\Project;

use Modules\Core\Models\Company;

use Modules\Clients\Models\Relation;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Clients\Enums\RelationType;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\Company;
use Modules\Core\Models\User;
use Modules\Projects\Enums\TaskStatus;
use Modules\Projects\Models\Project;
use Modules\Projects\Models\Task;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        $company = Company::query()->inRandomOrder()->first()
            ?? Company::factory()->create();

        $customer = Relation::query()
            ->where('relation_type', RelationType::CUSTOMER->value)
            ->inRandomOrder()
            ->first()
            ?? Relation::factory()->create([
                'relation_type' => RelationType::CUSTOMER->value,
            ]);

        $project = Project::query()
            ->where('customer_id', $customer->id)
            ->inRandomOrder()
            ->first()
            ?? Project::factory()->create(['customer_id' => $customer->id]);

        $taxRate = TaxRate::query()
            ->where('company_id', $company->id)
            ->inRandomOrder()
            ->first()
            ?? TaxRate::factory()->for($company)->create();

        $user = User::query()->inRandomOrder()->first();

        return [
            'company_id'  => $company->id,
            'customer_id' => $customer->id,
            'project_id'  => $project->id,
            'tax_rate_id' => $taxRate->id,
            'assigned_to' => $this->faker->boolean(50) ? optional($user)->id : null,
            'task_status' => $this->faker->randomElement(TaskStatus::cases())->value,
            'name'        => $this->faker->words(3, true),
            'due_at'      => $this->faker->dateTimeBetween('-3 year', '+2 year')->format('Y-m-d'),
            'price'       => $this->faker->randomFloat(2, 0, 100),
            'description' => null,
        ];
    }
}
