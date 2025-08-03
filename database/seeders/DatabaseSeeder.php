<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\Clients\Database\Seeders\RelationsSeeder;
use Modules\Core\Database\Seeders\OwnerUserSeeder;
use Modules\Core\Database\Seeders\PermissionsSeeder;
use Modules\Core\Database\Seeders\RoleHasPermissionsSeeder;
use Modules\Core\Database\Seeders\RolesSeeder;
use Modules\Core\Database\Seeders\UsersSeeder;
use Modules\Core\Models\Company;
use Modules\Expenses\Database\Seeders\ExpensesSeeder;
use Modules\Invoices\Database\Seeders\InvoicesSeeder;
use Modules\Payments\Database\Seeders\PaymentsSeeder;
use Modules\Products\Database\Seeders\ProductsSeeder;
use Modules\Projects\Database\Seeders\ProjectsSeeder;
use Modules\Projects\Database\Seeders\TasksSeeder;
use Modules\Quotes\Database\Seeders\QuotesSeeder;
use RuntimeException;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

class DatabaseSeeder extends Seeder
{
    private array $companyConfigs = [
        ['id' => 1, 'search_code' => 'ivplv2', 'name' => 'InvoicePlane Corporation'],
        ['id' => 2, 'search_code' => 'acme',   'name' => 'Acme Inc.'],
    ];

    private array $volumes = [
        'users'     => 15,
        'relations' => 25,
        'products'  => 15,
        'expenses'  => 15,
        'projects'  => 15,
        'tasks'     => 25,
        'quotes'    => 25,
        'invoices'  => 25,
        'payments'  => 15,
    ];

    public function run(): void
    {
        $this->truncateAll();
        $this->seedGlobal();

        $bar = $this->command->getOutput()->createProgressBar(\count($this->companyConfigs));
        $bar->setMessage('Companies');
        $bar->start();

        foreach ($this->companyConfigs as $cfg) {
            $company = Company::query()->updateOrCreate(
                ['id' => $cfg['id']],
                $cfg + [
                    'slug'             => Str::slug($cfg['name']),
                    'vat_number'       => 'US' . random_int(100_000_000, 999_999_999),
                    'id_number'        => (string) random_int(1_000_000_000, 9_999_999_999),
                    'coc_number'       => (string) random_int(1_000_000, 9_999_999),
                    'quote_template'   => 'default',
                    'invoice_template' => 'default',
                ]
            );

            $bar->advance();
        }
        $bar->finish();
        $this->command->newLine(2);

        $totalCompanies = Company::query()->count();
        $companyBar     = $this->command->getOutput()->createProgressBar($totalCompanies);
        $companyBar->setMessage('Seeding company data');
        $companyBar->start();

        Company::all()->each(callback: function (Company $company) use ($companyBar) {
            $p = ['company' => $company->id];

            $this->command->info("===== START Seeding company {$company->id} ({$company->name}) =====");

            $this->callWith(UsersSeeder::class, $p + ['count' => $this->volumes['users']]);
            $this->callWith(RelationsSeeder::class, $p + ['count' => $this->volumes['relations']]);

            $this->command->info('[DEBUG] Calling ProductsSeeder with: ' . json_encode($p + ['count' => $this->volumes['products']]));
            $this->callWith(ProductsSeeder::class, $p + ['count' => $this->volumes['products']]);

            $this->callWith(ExpensesSeeder::class, $p + ['count' => $this->volumes['expenses']]);

            $this->callWith(ProjectsSeeder::class, $p + ['count' => $this->volumes['projects']]);

            $this->callWith(TasksSeeder::class, $p + ['count' => $this->volumes['tasks']]);

            $this->callWith(QuotesSeeder::class, $p + ['count' => $this->volumes['quotes']]);

            $this->callWith(InvoicesSeeder::class, $p + ['count' => $this->volumes['invoices']]);

            $this->callWith(PaymentsSeeder::class, $p + ['count' => $this->volumes['payments']]);

            $this->command->info("===== END   Seeding company {$company->id} ({$company->name}) =====");

            $companyBar->advance();
        });

        $companyBar->finish();
        $this->command->newLine(2);

        $style = new OutputFormatterStyle('#429AE1', null, ['bold']);
        $this->command->getOutput()->getFormatter()->setStyle('brand', $style);
        $this->command->line('<brand>Done seeding the database</brand>');
        $this->command->newLine();

        if (Company::query()->count() !== count($this->companyConfigs)) {
            throw new RuntimeException('Unexpected company count.');
        }
    }

    private function seedGlobal(): void
    {
        $this->call(RolesSeeder::class);
        $this->call(PermissionsSeeder::class);
        $this->call(RoleHasPermissionsSeeder::class);
        $this->call(OwnerUserSeeder::class);
    }

    private function truncateAll(): void
    {
        $tables = collect(DB::select('SHOW TABLES'))
            ->map(fn ($row) => array_values((array) $row)[0])
            ->reject(fn ($t) => $t === 'migrations');

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        foreach ($tables as $table) {
            Schema::disableForeignKeyConstraints();
            DB::table($table)->truncate();
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
