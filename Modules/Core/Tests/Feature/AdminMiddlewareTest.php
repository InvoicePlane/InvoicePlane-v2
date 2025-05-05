<?php

namespace Modules\Core\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Modules\Core\Models\User;
use Modules\Core\Tests\AbstractTestCase;

class AdminMiddlewareTest extends AbstractTestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        Route::get('/middleware-test', function (): string {
            return 'test succeeded';
        })->middleware('admin');
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    public function admins_can_access_routes(): void
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/middleware-test')
            ->assertOk();
    }

    public function non_admins_cannot_access_routes(): void
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
        $nonAdmin = User::factory()->create();

        $this->actingAs($nonAdmin)
            ->get('/middleware-test')
            ->assertForbidden();
    }
}
