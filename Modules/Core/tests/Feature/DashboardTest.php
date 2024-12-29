<?php

namespace Modules\Core\tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Modules\Core\Models\User;
use Modules\Core\tests\AbstractTestCase;

class DashboardTest extends AbstractTestCase
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
    public function it_shows_dashboard_index(): void
    {
        $this->markTestIncomplete('Route product_families.index not defined');
        $user = User::factory()->create();
        $response = $this->actingAs($user, 'web')->get(route('filament.resources.dashboard.index'));
        $response->assertStatus(200);
        $response->assertSee('quote_overview');
        $response->assertSee('invoice_overview');
        $response->assertSee('recent_quotes');
        $response->assertSee('recent_invoices');
    }
}
