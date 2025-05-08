<?php

namespace Modules\Core\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Livewire\Livewire;
use Modules\Core\Models\Import;
use Modules\Core\Tests\AbstractTestCase;

//use Modules\Core\Filament\Resources\ImportResource\Pages\CreateImport;
//use Modules\Core\Filament\Resources\ImportResource\Pages\ManageImports;

class ImportTest extends AbstractTestCase
{
    use RefreshDatabase;
    use WithoutMiddleware;

    public function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    public function it_shows_import_details_index(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        //$this->authenticate();
        Import::factory()->create([
            'import_date' => '2022-04-01',
        ]);

        /** act */
        $component = Livewire::actingAs($this->superAdmin())->test(ListImports::class);

        /* assert */
        $component->assertSee('2022-04-01');
    }

    public function it_creates_an_import(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $data = Import::factory()->make()->toArray();

        /** act */
        $component = Livewire::actingAs($this->superAdmin())->test(CreateImport::class)->callTableAction('create', $data);

        /* assert */
        $component->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('imports', $data);
    }
}
