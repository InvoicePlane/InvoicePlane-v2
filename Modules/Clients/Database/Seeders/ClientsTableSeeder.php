<?php

namespace Modules\Clients\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Clients\Models\Client;
use Modules\Invoices\Models\Invoice;
use Modules\Projects\Models\Project;
use Modules\Quotes\Models\Quote;

class ClientsTableSeeder extends Seeder
{
    public function run(): void
    {
        Client::factory()->count(150)
            ->create()
            ->each(function ($client): void {
                $client->invoices()->saveMany(Invoice::factory(), rand(5, 10))->make();
                $client->projects()->saveMany(Project::factory(), rand(5, 10))->make();
                //TODO: something is breaking the seeder when quotes are generated
                //$client->quotes()->saveMany(Quote::factory(), rand(5, 10))->make();
            });
    }
}
