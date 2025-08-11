<?php

namespace Modules\Clients\Database\Seeders;

use Faker\Factory as Faker;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\Clients\Enums\CommunicationType;
use Modules\Clients\Enums\Gender;
use Modules\Clients\Enums\RelationStatus;
use Modules\Clients\Enums\RelationType;
use Modules\Clients\Models\Communication;
use Modules\Clients\Models\Contact;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\Company;

class CustomersSeeder extends \Modules\Core\Database\Seeders\AbstractSeeder
{
    protected array $firstNames = [
        'John', 'Emma', 'Michael', 'Sophia', 'William', 'Olivia', 'James', 'Ava', 'Robert', 'Isabella',
        'David', 'Mia', 'Joseph', 'Charlotte', 'Charles', 'Amelia', 'Thomas', 'Harper', 'Daniel', 'Evelyn',
    ];

    protected array $lastNames = [
        'Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis', 'Rodriguez', 'Martinez',
        'Hernandez', 'Lopez', 'Gonzalez', 'Wilson', 'Anderson', 'Thomas', 'Taylor', 'Moore', 'Jackson', 'Martin',
    ];

    protected array $companySuffixes = [
        'Inc', 'LLC', 'Ltd', 'Corp', 'Co', 'Group', 'Holdings', 'Partners', 'Enterprises', 'Solutions',
    ];

    protected $faker;

    public function __construct()
    {
        $this->faker = Faker::create();
    }

    public function run(?int $companyId = null): void
    {
        $query = Company::query();

        if ($companyId) {
            $query->where('id', $companyId);
        }

        $query->each(function (Company $company) {
            $existingCount = Relation::query()
                ->where('company_id', $company->id)
                ->whereIn('relation_type', [RelationType::CUSTOMER->value])
                ->count();

            if ($existingCount > 0) {
                Log::info("Skipping customers for company {$company->name} - already has {$existingCount} customers.");

                return;
            }

            Log::info("Creating customers for company: {$company->name}");

            // Create 10-20 customers per company
            $customerCount = rand(10, 20);

            for ($i = 0; $i < $customerCount; $i++) {
                $isCompany = rand(0, 1) === 1;

                if ($isCompany) {
                    $relation = $this->createCompanyCustomer($company);
                } else {
                    $relation = $this->createIndividualCustomer($company);
                }

                // Create primary contact for the relation
                $this->createPrimaryContact($company, $relation);
            }
        });
    }

    protected function createCompanyCustomer(Company $company): Relation
    {
        $name = $this->generateCompanyName();

        return Relation::factory()
            ->for($company)
            ->customer()
            ->create([
                'company_name'    => $name,
                'trading_name'    => $name,
                'unique_name'     => Str::slug($name),
                'relation_status' => $this->randomStatus(),
                'relation_number' => 'CUST-' . mb_strtoupper(Str::random(8)),
                'language'        => 'en',
                'registered_at'   => now()->subDays(random_int(1, 365)),
            ]);
    }

    protected function createIndividualCustomer(Company $company): Relation
    {
        $firstName = $this->firstNames[array_rand($this->firstNames)];
        $lastName  = $this->lastNames[array_rand($this->lastNames)];
        $fullName  = "{$firstName} {$lastName}";

        return Relation::factory()
            ->for($company)
            ->customer()
            ->create([
                'company_name'    => $fullName,
                'relation_status' => $this->randomStatus(),
                'relation_number' => 'CUST-' . mb_strtoupper(Str::random(8)),
                'registered_at'   => now()->subDays(random_int(1, 365)),
            ]);
    }

    protected function generateCompanyName(): string
    {
        $prefixes = ['Global', 'National', 'International', 'Advanced', 'First', 'United', 'American', 'European', 'Pacific', 'Atlantic'];
        $suffix   = $this->companySuffixes[array_rand($this->companySuffixes)];
        $industry = ['Tech', 'Solutions', 'Systems', 'Industries', 'Services', 'Ventures', 'Holdings', 'Group', 'Partners', 'Enterprises'];
        $product  = ['Tech', 'Data', 'Cloud', 'Digital', 'Info', 'Net', 'Web', 'Soft', 'Mobile', 'Smart'];

        $name = [];

        if (random_int(0, 1) === 1) {
            $name[] = $prefixes[array_rand($prefixes)];
        }

        $name[] = $product[array_rand($product)];
        $name[] = $industry[array_rand($industry)];

        $name[] = $suffix;

        return implode(' ', $name);
    }

    protected function createPrimaryContact(Company $company, Relation $relation): void
    {
        $isCompany = ! empty($relation->company_name);
        $gender    = $this->faker->randomElement(Gender::cases())->value;

        $contact = Contact::create([
            'company_id'  => $company->id,
            'relation_id' => $relation->id,
            'first_name'  => $isCompany ? $this->faker->firstName($gender) : $relation->first_name,
            'last_name'   => $isCompany ? $this->faker->lastName : $relation->last_name,
            'gender'      => $isCompany ? $gender : ($relation->gender ?? $gender),
            'default_to'  => true,
            'default_cc'  => false,
            'default_bcc' => false,
        ]);

        // Create email communication
        Communication::create([
            'company_id'             => $company->id,
            'communicationable_type' => Contact::class,
            'communicationable_id'   => $contact->id,
            'is_primary'             => true,
            'communication_type'     => CommunicationType::EMAIL->value,
            'communication_value'    => $this->faker->unique()->safeEmail,
        ]);

        // Create phone communication
        Communication::create([
            'company_id'             => $company->id,
            'communicationable_type' => Contact::class,
            'communicationable_id'   => $contact->id,
            'is_primary'             => true,
            'communication_type'     => CommunicationType::PHONE->value,
            'communication_value'    => $this->faker->phoneNumber,
        ]);

        // Update relation with primary contact
        $relation->update(['primary_contact_id' => $contact->id]);
    }

    private function randomStatus(): string
    {
        $statuses = [
            RelationStatus::ACTIVE->value,
            RelationStatus::INACTIVE->value,
        ];

        return $statuses[array_rand($statuses)];
    }
}
