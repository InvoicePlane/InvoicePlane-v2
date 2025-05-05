<?php

namespace Modules\Core\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Modules\Core\Models\User;
use Modules\Core\Tests\AbstractTestCase;

class WelcomeViewTest extends AbstractTestCase
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


    public function it_shows_welcome_view(): void
    {
        $this->markTestIncomplete('core web routes not loaded yet?');
        $user     = User::factory()->create();
        $response = $this->actingAs(user: $user, guard: 'web')->get(route('welcome.index'));
        $response->assertSuccessful();
        $response->assertSee('InvoicePlane');
        $response->assertSee('Please install InvoicePlane');
        $response->assertSee('Setup');
        $response->assertSee('Get Help');
    }
}
