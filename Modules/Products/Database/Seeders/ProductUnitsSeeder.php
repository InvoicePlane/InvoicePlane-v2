<?php

namespace Modules\Products\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Models\Company;
use Modules\Products\Models\ProductUnit;

class ProductUnitsSeeder extends Seeder
{
    protected array $defaultUnits = [
        ['unit_name' => 'Piece', 'unit_name_plrl' => 'pcs'],
        ['unit_name' => 'Kilogram', 'unit_name_plrl' => 'kgs'],
        ['unit_name' => 'Gram', 'unit_name_plrl' => 'gms'],
        ['unit_name' => 'Liter', 'unit_name_plrl' => 'Liters'],
        ['unit_name' => 'Meter', 'unit_name_plrl' => 'mtrs'],
        ['unit_name' => 'Box', 'unit_name_plrl' => 'boxes'],
        ['unit_name' => 'Set', 'unit_name_plrl' => 'sets'],
        ['unit_name' => 'Pair', 'unit_name_plrl' => 'pairs'],
        ['unit_name' => 'Dozen', 'unit_name_plrl' => 'dozens'],
        ['unit_name' => 'Hour', 'unit_name_plrl' => 'hrs'],
    ];

    public function run(?int $companyId = null): void
    {
        $query = Company::query();

        if ($companyId) {
            $query->where('id', $companyId);
        }

        $query->each(function (Company $company) {
            $existingCount = ProductUnit::query()->where('company_id', $company->id)->count();

            if ($existingCount > 0) {
                $this->command->info("Skipping product units for company {$company->name} - already has {$existingCount} units.");

                return;
            }

            $this->command->info("Creating product units for company: {$company->name}");

            foreach ($this->defaultUnits as $unit) {
                ProductUnit::factory()
                    ->for($company)
                    ->create([
                        'unit_name'      => $unit['unit_name'],
                        'unit_name_plrl' => $unit['unit_name_plrl'],
                    ]);
            }
        });
    }
}
