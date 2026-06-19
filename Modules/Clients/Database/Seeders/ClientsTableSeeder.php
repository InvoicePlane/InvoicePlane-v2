<?php

namespace Modules\Clients\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Clients\Models\Client;
use Modules\Invoices\Models\Invoice;
use Modules\Projects\Models\Project;

class ClientsTableSeeder extends Seeder
{
    public function run(): void
    {
        Client::factory()->count(15)
            ->create()
            ->each(function ($client): void {
                $client->invoices()->saveMany(Invoice::factory(), rand(5, 10))->make();
                $client->projects()->saveMany(Project::factory(), rand(5, 10))->make();
                //TODO: something is breaking the seeder when quotes are generated
                //$client->quotes()->saveMany(Quote::factory(), rand(5, 10))->make();
            });
    }
}
