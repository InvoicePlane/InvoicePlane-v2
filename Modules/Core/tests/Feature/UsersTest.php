<?php

namespace Modules\Core\tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Modules\Core\tests\AbstractTestCase;
use Modules\Core\tests\ApiTestTrait;

/** @group features */
class UsersTest extends AbstractTestCase
{
    use ApiTestTrait;
    use RefreshDatabase;
    use WithoutMiddleware;
    // endregion

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    // region CRUD Tests

    /** @test */
    public function it_creates_a_user(): void
    {
        $this->markTestSkipped('Not implemented yet');
        // $this->authenticate();

        /**
         * @payload
         * {
         *    "user_type": "client",
         *    "user_active": true,
         *    "user_name": "jdoe",
         *    "user_company": "Example Inc",
         *    "user_address_1": "123 Main Street",
         *    "user_city": "Somewhere",
         *    "user_country": "USA",
         *    "email": "jdoe@example.com",
         *    "user_password": "securepassword",
         *    "user_language": "en"
         * }
         */
        $payload = [
            'user_type'      => 'client',
            'user_active'    => true,
            'user_name'      => 'jdoe',
            'user_company'   => 'Example Inc',
            'user_address_1' => '123 Main Street',
            'user_city'      => 'Somewhere',
            'user_country'   => 'USA',
            'email'          => 'jdoe@example.com',
            'user_password'  => bcrypt('securepassword'),
            'user_language'  => 'en',
        ];

        // Act
        $response = $this->post(route('filament.ivpl.resources.filament.resources.users.store'), $payload);

        // Assert
        $response->assertStatus(201);
        $this->assertDatabaseHas('users', ['email' => 'jdoe@example.com']);
    }

    /** @test */
    public function it_fails_to_create_a_user_without_required_fields(): void
    {
        $this->markTestSkipped('Not implemented yet');
        // $this->authenticate();

        /**
         * @payload
         * {
         *    "user_type": null,
         *    "email": null,
         *    "user_password": null
         * }
         */
        $payload = [
            'user_type'     => null,
            'email'         => null,
            'user_password' => null,
        ];

        // Act
        $response = $this->post(route('filament.ivpl.resources.filament.resources.users.store'), $payload);

        // Assert
        $response->assertStatus(422); // Validation error
    }

    /** @test */
    public function it_updates_a_user(): void
    {
        $this->markTestSkipped('Not implemented yet');
        // $this->authenticate();

        /**
         * @payload
         * {
         *    "user_name": "updated_user",
         *    "user_company": "Updated Inc"
         * }
         */
        $user = User::factory()->create();

        $payload = [
            'user_name'    => 'updated_user',
            'user_company' => 'Updated Inc',
        ];

        // Act
        $response = $this->patch(route('filament.ivpl.resources.filament.resources.users.update', $user->user_id), $payload);

        // Assert
        $response->assertStatus(200);
        $this->assertDatabaseHas('users', ['user_id' => $user->user_id, 'user_name' => 'updated_user']);
    }

    /** @test */
    public function it_deletes_a_user(): void
    {
        $this->markTestSkipped('Not implemented yet');
        // $this->authenticate();

        /**
         * @payload
         * {
         *    "user_id": 1
         * }
         */
        $user = User::factory()->create();

        // Act
        $response = $this->delete(route('filament.ivpl.resources.filament.resources.users.destroy', $user->user_id));

        // Assert
        $response->assertStatus(200);
        $this->assertDatabaseMissing('users', ['user_id' => $user->user_id]);
    }
}
