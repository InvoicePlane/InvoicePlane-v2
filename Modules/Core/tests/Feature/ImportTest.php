<?php

namespace Modules\Core\tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Modules\Core\Models\Import;
use Modules\Core\Models\User;
use Modules\Core\tests\AbstractTestCase;

class ImportTest extends AbstractTestCase
{
    use RefreshDatabase;
    use WithoutMiddleware;

    public function setUp(): void
    {
        parent::setUp();
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * @test
     */
    public function it_shows_import_details_index(): void
    {
        $user = User::factory()->create();
        Import::factory()->create([
            'import_date' => '2022-04-01',
        ]);
        $response = $this->actingAs(user: $user, guard: 'web')->get(route('filament.resources.imports.index'));
        $response->assertStatus(200);
        $response->assertSee('2022-04-01');
    }
}
