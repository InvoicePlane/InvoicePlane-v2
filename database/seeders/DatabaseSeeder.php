<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Clients\Database\Seeders\RelationsSeeder;
use Modules\Core\Database\Seeders\OwnerUserSeeder;
use Modules\Core\Database\Seeders\PermissionsSeeder;
use Modules\Core\Database\Seeders\RoleHasPermissionsSeeder;
use Modules\Core\Database\Seeders\RolesSeeder;
use Modules\Core\Database\Seeders\TaxRatesSeeder;
use Modules\Core\Database\Seeders\UsersSeeder;
use Modules\Core\Models\Company;
use Modules\Core\Models\User;
use Modules\Expenses\Database\Seeders\ExpensesSeeder;
use Modules\Invoices\Database\Seeders\InvoicesSeeder;
use Modules\Payments\Database\Seeders\PaymentsSeeder;
use Modules\Products\Database\Seeders\ProductsSeeder;
use Modules\Projects\Database\Seeders\ProjectsSeeder;
use Modules\Projects\Database\Seeders\TasksSeeder;
use Modules\Quotes\Database\Seeders\QuotesSeeder;
use Modules\Subscriptions\Database\Seeders\SubscriptionSeeder;
use RuntimeException;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

class DatabaseSeeder extends Seeder
{
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

    private array $companyConfigs;

    public function run(): void
    {
        $this->companyConfigs = $this->generateCompanyConfigs(10);

        $this->truncateAll();
        $this->seedGlobal();

        $bar = $this->command->getOutput()->createProgressBar(count($this->companyConfigs));
        $bar->setMessage('Companies');
        $bar->start();

        foreach ($this->companyConfigs as $cfg) {
            Company::query()->updateOrCreate(
                ['id' => $cfg['id']],
                $cfg
            );
            $bar->advance();
        }

        $bar->finish();
        $this->command->newLine(2);

        $admin  = User::query()->where('email', 'admin@invoiceplane.com')->first();
        $ivplv2 = Company::query()->whereRaw('LOWER(search_code) = ?', ['ivplv2'])->first();
        if ($admin && $ivplv2) {
            $admin->companies()->syncWithoutDetaching([$ivplv2->id]);
        }

        $totalCompanies = Company::query()->count();
        $companyBar     = $this->command->getOutput()->createProgressBar($totalCompanies);
        $companyBar->setMessage('Seeding company data');
        $companyBar->start();

        Company::all()->each(callback: function (Company $company) use ($companyBar) {
            $p = ['company' => $company->id];

            $this->command->newLine(2);
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

            $this->call(SubscriptionSeeder::class);

            (new TaxRatesSeeder())->buildOne($company->id);

            $this->command->info("===== END   Seeding company {$company->id} ({$company->name}) =====");

            $companyBar->advance();
        });

        $companyBar->finish();
        $this->command->newLine(2);

        $style = new OutputFormatterStyle('#429AE1', null, ['bold']);
        $this->command->getOutput()->getFormatter()->setStyle('brand', $style);
        $this->command->line('<brand>Done seeding the database</brand>');

        $this->command->newLine(2);

        $randomUser = User::query()
            ->where('email', '!=', 'admin@invoiceplane.com')
            ->inRandomOrder()
            ->first();

        $this->command->table(
            ['Role', 'Email', 'Password'],
            [
                ['super_admin', 'admin@invoiceplane.com', 'password'],
                ['user', $randomUser?->email ?? '(none)', 'password'],
            ]
        );

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

    private function generateCompanyConfigs(int $extraCompanyCount = 5): array
    {
        $invoicePlaneCorp = Company::factory()->make([
            'id'          => 22,
            'search_code' => 'ivplv2',
            'name'        => 'InvoicePlane Corporation',
            'slug'        => 'invoiceplane-corporation',
        ])->toArray();

        $usedIds = [22];
        $extra   = [];
        for ($n = 0; $n < $extraCompanyCount; $n++) {
            do {
                $id = random_int(1, 99);
            } while (in_array($id, $usedIds, true) || $id === 22);
            $usedIds[] = $id;

            $company = Company::factory()->make(['id' => $id]);
            $extra[] = $company->toArray();
        }

        return array_merge([$invoicePlaneCorp], $extra);
    }
}
