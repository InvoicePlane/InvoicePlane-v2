<?php

namespace Modules\Products\Database\Seeders;

use Modules\Core\Models\Company;
use Modules\Products\Models\ProductUnit;

class ProductUnitsSeeder extends \Modules\Core\Database\Seeders\AbstractSeeder
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
            $this->command->info("Seeding product units for company: {$company->name}");

            // First, check if we already have units for this company
            $existingUnits = ProductUnit::query()
                ->where('company_id', $company->id)
                ->pluck('unit_name')
                ->toArray();

            $created = 0;
            $skipped = 0;

            $bar = $this->command->getOutput()->createProgressBar(count($this->defaultUnits));
            $bar->start();

            foreach ($this->defaultUnits as $unit) {
                // Skip if this unit already exists for the company
                if (in_array($unit['unit_name'], $existingUnits, true)) {
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                ProductUnit::updateOrCreate(
                    [
                        'company_id' => $company->id,
                        'unit_name'  => $unit['unit_name'],
                    ],
                    [
                        'unit_name_plrl' => $unit['unit_name_plrl'],
                    ]
                );
                $created++;
                $bar->advance();
            }

            $bar->finish();
            $this->command->newLine(2);
            $this->command->info(sprintf(
                'Product units for %s: %d created, %d already existed',
                $company->name,
                $created,
                $skipped
            ));
        });
    }
}
