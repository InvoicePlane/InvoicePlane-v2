<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Models\EmailTemplate;

class EmailTemplatesTableSeeder extends Seeder
{
    public function run(): void
    {
        EmailTemplate::factory()->count(5)->create();
    }
}
