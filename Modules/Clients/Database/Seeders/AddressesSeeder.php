<?php

namespace Modules\Clients\Database\Seeders;

use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Clients\Models\Address;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\Company;

class AddressesSeeder extends Seeder
{
    protected array $usStates = [
        'AL' => 'Alabama', 'AK' => 'Alaska', 'AZ' => 'Arizona', 'AR' => 'Arkansas', 'CA' => 'California',
        'CO' => 'Colorado', 'CT' => 'Connecticut', 'DE' => 'Delaware', 'FL' => 'Florida', 'GA' => 'Georgia',
        'HI' => 'Hawaii', 'ID' => 'Idaho', 'IL' => 'Illinois', 'IN' => 'Indiana', 'IA' => 'Iowa',
        'KS' => 'Kansas', 'KY' => 'Kentucky', 'LA' => 'Louisiana', 'ME' => 'Maine', 'MD' => 'Maryland',
        'MA' => 'Massachusetts', 'MI' => 'Michigan', 'MN' => 'Minnesota', 'MS' => 'Mississippi',
        'MO' => 'Missouri', 'MT' => 'Montana', 'NE' => 'Nebraska', 'NV' => 'Nevada', 'NH' => 'New Hampshire',
        'NJ' => 'New Jersey', 'NM' => 'New Mexico', 'NY' => 'New York', 'NC' => 'North Carolina',
        'ND' => 'North Dakota', 'OH' => 'Ohio', 'OK' => 'Oklahoma', 'OR' => 'Oregon', 'PA' => 'Pennsylvania',
        'RI' => 'Rhode Island', 'SC' => 'South Carolina', 'SD' => 'South Dakota', 'TN' => 'Tennessee',
        'TX' => 'Texas', 'UT' => 'Utah', 'VT' => 'Vermont', 'VA' => 'Virginia', 'WA' => 'Washington',
        'WV' => 'West Virginia', 'WI' => 'Wisconsin', 'WY' => 'Wyoming',
    ];

    protected array $addressTypes = [
        'billing',
        'office',
        'home',
        'primary',
        'shipping',
        'warehouse',
        'other',
    ];

    public function run(?int $companyId = null): void
    {
        $faker = Faker::create();
        $query = Company::query();

        if ($companyId) {
            $query->where('id', $companyId);
        }

        $query->each(function (Company $company) use ($faker) {
            $existingCount = Address::query()->where('company_id', $company->id)->count();

            if ($existingCount > 0) {
                $this->command->info("Skipping addresses for company {$company->name} - already has {$existingCount} addresses.");

                return;
            }

            $this->command->info("Creating addresses for company: {$company->name}");

            $customers = Relation::query()->where('company_id', $company->id)->get();

            if ($customers->isEmpty()) {
                $this->command->warn("No customers found for company {$company->name}. Creating some...");
                $this->call(CustomersSeeder::class, ['companyId' => $company->id]);
                $customers = Relation::query()->where('company_id', $company->id)->get();
            }

            $addresses = [];

            foreach ($customers as $customer) {
                $addressCount = rand(1, 3);

                for ($i = 0; $i < $addressCount; $i++) {
                    $isPrimary = $i === 0;
                    $type      = $isPrimary ? 'primary' : $this->addressTypes[array_rand($this->addressTypes)];

                    $state    = array_rand($this->usStates);
                    $city     = $faker->city;
                    $zip      = $faker->postcode;
                    $address1 = $faker->streetAddress;
                    $address2 = rand(0, 1) === 1 ? $faker->secondaryAddress : null;

                    $addresses[] = [
                        'company_id'        => $company->id,
                        'addressable_type'  => get_class($customer),
                        'addressable_id'    => $customer->id,
                        'type'              => $type,
                        'address_1'         => $address1,
                        'address_2'         => $address2,
                        'number'            => $faker->buildingNumber,
                        'postal_code'       => $zip,
                        'city'              => $city,
                        'state_or_province' => $state,
                        'country'           => 'US',
                    ];
                }
            }

            foreach (array_chunk($addresses, 50) as $chunk) {
                DB::table('addresses')->insert($chunk);
            }

            $this->command->info(sprintf(
                'Created %d addresses for company: %s',
                count($addresses),
                $company->name
            ));
        });
    }
}
