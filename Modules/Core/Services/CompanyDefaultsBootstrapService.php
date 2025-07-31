<?php

namespace Modules\Core\Services;

use Modules\Core\Database\Seeders\DocumentGroupsSeeder;
use Modules\Core\Database\Seeders\EmailTemplatesSeeder;
use Modules\Core\Database\Seeders\TaxRatesSeeder;
use Modules\Expenses\Database\Seeders\ExpenseCategoriesSeeder;
use Modules\Products\Database\Seeders\ProductCategoriesSeeder;
use Modules\Products\Database\Seeders\ProductUnitsSeeder;

class CompanyDefaultsBootstrapService
{
    public static function bootstrap(int $companyId): void
    {
        (new DocumentGroupsSeeder())->run($companyId);

        (new EmailTemplatesSeeder())->run($companyId);

        (new TaxRatesSeeder())->run($companyId);

        (new ProductCategoriesSeeder())->run($companyId);

        (new ProductUnitsSeeder())->run($companyId);

        (new ExpenseCategoriesSeeder())->run($companyId);
    }
}
