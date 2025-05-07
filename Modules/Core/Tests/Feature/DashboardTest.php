<?php

namespace Modules\Core\Tests\Feature;

use Modules\Core\Tests\Feature\DashboardTest;

use Modules\Core\Tests\AbstractTestCase;

use Modules\Core\Models\User;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class DashboardTest extends AbstractTestCase
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

    public function it_shows_dashboard_index(): void
    {
        $this->markTestIncomplete('Route product_families.index not defined');
        $user     = User::factory()->create();
        $response = $this->actingAs($user, 'web')->get(route('filament.ivpl.resources.filament.resources.dashboard.index'));
        $response->assertSuccessful();
        $response->assertSee('quote_overview');
        $response->assertSee('invoice_overview');
        $response->assertSee('recent_quotes');
        $response->assertSee('recent_invoices');
    }
}
