<?php

namespace Modules\Core\tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Modules\Core\Models\User;
use Modules\Core\tests\AbstractTestCase;

class WelcomeViewTest extends AbstractTestCase
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
    public function it_shows_welcome_view(): void
    {
        $this->markTestIncomplete('core web routes not loaded yet?');
        $user = User::factory()->create();
        $response = $this->actingAs(user: $user, guard: 'web')->get(route('welcome.index'));
        $response->assertStatus(200);
        $response->assertSee('InvoicePlane');
        $response->assertSee('Please install InvoicePlane');
        $response->assertSee('Setup');
        $response->assertSee('Get Help');
    }
}
