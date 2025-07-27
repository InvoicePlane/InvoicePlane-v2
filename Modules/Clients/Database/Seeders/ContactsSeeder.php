<?php

namespace Modules\Clients\Database\Seeders;

use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Clients\Models\Contact;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\Company;

class ContactsSeeder extends Seeder
{
    protected array $jobTitles = [
        'CEO', 'CFO', 'CTO', 'Director', 'Manager', 'Supervisor', 'Coordinator',
        'Account Manager', 'Sales Representative', 'Customer Service', 'Purchasing Manager',
        'Operations Manager', 'Finance Manager', 'HR Manager', 'IT Manager',
        'Marketing Manager', 'Project Manager', 'Office Manager', 'Executive Assistant',
        'Department Head', 'Team Lead',
    ];

    protected array $departments = [
        'Executive', 'Management', 'Sales', 'Marketing', 'Finance', 'Human Resources',
        'Information Technology', 'Operations', 'Customer Service', 'Purchasing',
        'Research & Development', 'Production', 'Quality Assurance', 'Logistics',
        'Administration', 'Legal', 'Compliance', 'Business Development', 'Public Relations',
    ];

    public function run(?int $companyId = null): void
    {
        $faker = Faker::create();
        $query = Company::query();

        if ($companyId) {
            $query->where('id', $companyId);
        }

        $query->each(function (Company $company) use ($faker) {
            $existingCount = Contact::query()->where('company_id', $company->id)->count();

            if ($existingCount > 0) {
                $this->command->info("Skipping contacts for company {$company->name} - already has {$existingCount} contacts.");

                return;
            }

            $this->command->info("Creating contacts for company: {$company->name}");

            $customers = Relation::query()->where('company_id', $company->id)
                ->whereIn('relation_type', ['customer', 'both'])
                ->get();

            if ($customers->isEmpty()) {
                $this->command->warn("No customers found for company {$company->name}. Creating some...");
                $this->call(CustomersSeeder::class, ['companyId' => $company->id]);
                $customers = Relation::query()->where('company_id', $company->id)
                    ->whereIn('relation_type', ['customer', 'both'])
                    ->get();
            }

            $contacts = [];
            $now      = now();

            foreach ($customers as $customer) {
                $contactCount = $customer->type === 'company' ? rand(1, 3) : 1;

                for ($i = 0; $i < $contactCount; $i++) {
                    $isPrimary = $i === 0;
                    $gender    = $faker->randomElement(['male', 'female']);
                    $firstName = $faker->firstName($gender);
                    $lastName  = $faker->lastName;

                    $contacts[] = [
                        'company_id'  => $company->id,
                        'relation_id' => $customer->id,
                        'first_name'  => $firstName,
                        'last_name'   => $lastName,
                        'gender'      => $gender,
                        'default_to'  => $isPrimary ? true : $faker->boolean(20), // 20% chance for non-primary
                        'default_cc'  => $isPrimary ? $faker->boolean(30) : false, // Primary might be CC
                        'default_bcc' => false,
                    ];
                }
            }

            foreach (array_chunk($contacts, 100) as $chunk) {
                DB::table('contacts')->insert($chunk);
            }

            $this->command->info(sprintf(
                'Created %d contacts for company: %s',
                count($contacts),
                $company->name
            ));
        });
    }
}
