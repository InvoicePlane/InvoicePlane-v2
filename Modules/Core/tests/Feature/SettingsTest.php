<?php

namespace Modules\Core\tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Modules\Core\Models\User;
use Modules\Core\tests\AbstractTestCase;

class SettingsTest extends AbstractTestCase
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

    /** @test */
    public function it_shows_settings_index(): void
    {
        $this->markTestSkipped();
        $user = User::factory()->create();
        $response = $this->actingAs(user: $user, guard: 'web')->get(route('filament.ivpl.resources.filament.resources.settings.index'));
        $response->assertStatus(200);
        $response->assertSee('general');
        $response->assertSee('invoices');
        $response->assertSee('quotes');
        $response->assertSee('taxes');
        $response->assertSee('email');
        $response->assertSee('online_payment');
        $response->assertSee('projects');
    }
}
