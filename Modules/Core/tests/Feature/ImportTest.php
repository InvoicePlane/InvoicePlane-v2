<?php

namespace Modules\Core\tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Livewire\Livewire;
use Modules\Core\Filament\Resources\ImportResource\Pages\CreateImport;
use Modules\Core\Filament\Resources\ImportResource\Pages\ManageImports;
use Modules\Core\Models\Import;
use Modules\Core\tests\AbstractTestCase;

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

    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function it_shows_import_details_index(): void
    {
        //$this->authenticate();
        Import::factory()->create([
            'import_date' => '2022-04-01',
        ]);

        Livewire::test(ManageImports::class)
            ->assertSee('2022-04-01');
    }

    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function it_creates_an_import(): void
    {
        $data = Import::factory()->make()->toArray();

        Livewire::test(CreateImport::class)
            ->callTableAction('create', $data)
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('imports', $data);
    }
}
