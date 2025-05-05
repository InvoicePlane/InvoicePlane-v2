<?php

namespace Modules\Clients\Database\Seeders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Modules\Clients\Enums\RelationType;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\Company;
use Modules\Expenses\Models\Expense;
use Modules\Invoices\Models\Invoice;
use Modules\Projects\Models\Project;
use Modules\Projects\Models\Task;
use Modules\Quotes\Models\Quote;

class CustomersSeeder extends Seeder
{
    public function run(): void
    {
        Company::all()->each(function (Model $model): void {
            /** @var Company $company */
            $company = $model;

            Relation::factory()
                ->count(10)
                ->create([
                    'company_id'    => $company->id,
                    'relation_type' => RelationType::CUSTOMER->value,
                ])
                ->each(function (Model $model) use ($company): void {
                    /** @var Relation $customer */
                    $customer = $model;

                    Invoice::factory()
                        ->count(random_int(5, 10))
                        ->create([
                            'company_id'  => $company->id,
                            'customer_id' => $customer->id,
                        ]);

                    Project::factory()
                        ->count(random_int(3, 5))
                        ->create([
                            'company_id'  => $company->id,
                            'customer_id' => $customer->id,
                        ])
                        ->each(function (Model $model) use ($company, $customer): void {
                            /** @var Project $project */
                            $project = $model;

                            Task::factory()
                                ->count(random_int(3, 7))
                                ->create([
                                    'company_id'  => $company->id,
                                    'customer_id' => $customer->id,
                                    'project_id'  => $project->id,
                                ]);
                        });
                });

            Relation::factory()
                ->count(25)
                ->create([
                    'company_id'    => $company->id,
                    'relation_type' => RelationType::PROSPECT->value,
                ])
                ->each(function (Model $model) use ($company): void {
                    /** @var Relation $prospect */
                    $prospect = $model;

                    Quote::factory()
                        ->count(random_int(3, 7))
                        ->create([
                            'company_id'  => $company->id,
                            'prospect_id' => $prospect->id,
                        ]);
                });

            Relation::factory()
                ->count(12)
                ->create([
                    'company_id'    => $company->id,
                    'relation_type' => RelationType::VENDOR->value,
                ])
                ->each(function (Model $model) use ($company): void {
                    /** @var Relation $vendor */
                    $vendor = $model;

                    Expense::factory()
                        ->count(random_int(5, 10))
                        ->create([
                            'company_id' => $company->id,
                            'vendor_id'  => $vendor->id,
                        ]);
                });
        });
    }
}
