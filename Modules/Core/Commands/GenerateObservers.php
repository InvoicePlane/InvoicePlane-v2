<?php

namespace Modules\Core\Commands;

use Modules\Projects\Models\Task;

use Modules\Core\Support\Results\Payments;

use Modules\Products\Models\ProductUnit;

use Modules\Payments\Models\Payment;

use Modules\Invoices\Models\Invoice;

use Modules\Core\Support\Results\Quotes;

use Modules\Clients\Models\Contact;

use Modules\Quotes\Models\Quote;

use Modules\Core\Models\User;

use Modules\Core\Support\Results\Clients;

use Modules\Products\Models\ProductCategory;

use Modules\Core\Commands\GenerateObservers;

use Modules\Products\Models\Product;

use Modules\Projects\Models\Project;

use Modules\Core\Support\Results\Invoices;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateObservers extends Command
{
    protected $signature = 'ip:generate-observers';

    protected $description = 'Generate observer classes for core models across modules';

    public function handle(): void
    {
        $modules = [
            'Clients'  => ['Contact', 'Customer'],
            'Projects' => ['Project', 'Task'],
            'Products' => ['Product', 'ProductCategory', 'ProductUnit'],
            'Invoices' => ['Invoice'],
            'Quotes'   => ['Quote'],
            'Payments' => ['Payment'],
            'Users'    => ['User'],
        ];

        $stub = File::get(base_path('stubs/observer.stub'));

        foreach ($modules as $module => $models) {
            foreach ($models as $model) {
                $directory = base_path("Modules/{$module}/Observers");
                $filename  = "{$directory}/{$model}Observer.php";

                if (File::exists($filename)) {
                    $this->warn("Skipped: {$filename} already exists.");
                    continue;
                }

                File::ensureDirectoryExists($directory);

                $output = str_replace(
                    ['{{ module }}', '{{ model }}'],
                    [$module, $model],
                    $stub
                );

                File::put($filename, $output);
                $this->info("Created: {$filename}");
            }
        }
    }
}
