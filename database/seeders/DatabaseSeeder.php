<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Clients\Database\Seeders\ClientsTableSeeder;
use Modules\Core\Database\Seeders\EmailTemplatesTableSeeder;
use Modules\Core\Database\Seeders\TaxRatesTableSeeder;
use Modules\Core\Database\Seeders\UsersTableSeeder;
use Modules\Inventory\Database\Seeders\FamiliesTableSeeder;
use Modules\Inventory\Database\Seeders\ProductInventoriesTableSeeder;
use Modules\Inventory\Database\Seeders\ProductUnitsTableSeeder;
use Modules\Invoices\Database\Seeders\InvoiceGroupsTableSeeder;
use Modules\Invoices\Database\Seeders\InvoicesTableSeeder;
use Modules\Payments\Database\Seeders\PaymentMethodsTableSeeder;
use Modules\Payments\Database\Seeders\PaymentsTableSeeder;
use Modules\Projects\Database\Seeders\ProjectsTableSeeder;
use Modules\Projects\Database\Seeders\TasksTableSeeder;
use Modules\Quotes\Database\Seeders\QuotesTableSeeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(EmailTemplatesTableSeeder::class);
        $this->call(FamiliesTableSeeder::class);
        $this->call(ProductUnitsTableSeeder::class);
        $this->call(InvoiceGroupsTableSeeder::class);
        $this->call(PaymentMethodsTableSeeder::class);
        $this->call(TaxRatesTableSeeder::class);

        $this->call(UsersTableSeeder::class);
        $this->call(ClientsTableSeeder::class);

        $this->call(ProductInventoriesTableSeeder::class);
        $this->call(ProjectsTableSeeder::class);
        $this->call(TasksTableSeeder::class);
        $this->call(InvoicesTableSeeder::class);
        $this->call(PaymentsTableSeeder::class);
        $this->call(QuotesTableSeeder::class);
    }
}
