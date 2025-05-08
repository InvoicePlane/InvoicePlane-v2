<?php

namespace Modules\Core\Tests\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
// use Laravel\Sanctum\Sanctum;
use Modules\Core\Models\User;
use Modules\Core\Tests\AbstractTestCase;
use Modules\Core\Tests\ApiTestTrait;

class UsersApiTest extends AbstractTestCase
{
    use ApiTestTrait;
    use RefreshDatabase;
    use WithoutMiddleware;
    // endregion

    public function setUp(): void
    {
        parent::setUp();
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    // region CRUD Tests

    #[Group('crud')]
    public function it_returns_users_index(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $user = User::factory()->create();

        User::factory(5)->create([
            'user_type'    => User::ADMIN,
            'user_active'  => true,
            'user_name'    => '::user_name::',
            'user_company' => '::localhost corporation::',
        ]);
        Sanctum::actingAs($user);

        $response = $this->get(route('api.users.index'));
        $response->assertSuccessful();

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'user_type',
                    'is_active',
                    'language',
                    'name',
                    'company',
                    'email',
                    'contact',
                ],
            ],
        ]);
        $response->assertJsonFragment(['name' => '::user_name::']);
        $response->assertJsonFragment(['company' => '::localhost corporation::']);
    }

    #[Group('crud')]
    public function it_can_retrieve_a_list_of_users(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $this->markTestSkipped('Not implemented yet');
        // $this->withAuthentication();

        /*
         * @payload
         * [
         *    {
         *      "user_id": 1,
         *      "user_name": "jdoe",
         *      "user_company": "Example Inc",
         *      "email": "jdoe@example.com"
         *    },
         *    {
         *      "user_id": 2,
         *      "user_name": "asmith",
         *      "user_company": "Tech Co",
         *      "email": "asmith@example.com"
         *    }
         * ]
         */
        User::factory()->count(3)->create();

        // Act
        $response = $this->getJson(route('api.users.index'));

        // Assert
        $response->assertSuccessful()
            ->assertJsonStructure([['user_id', 'user_name', 'user_company', 'email']]);
    }

    #[Group('crud')]
    public function it_fails_to_retrieve_users_without_authentication(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $this->markTestSkipped('Not implemented yet');

        // Act
        $response = $this->getJson(route('api.users.index'));

        // Assert
        $response->assertStatus(401); // Unauthorized
    }

    #[Group('crud')]
    public function it_creates_a_user(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $adminUser = User::factory()->create([
            'user_email' => 'admin@gmail.com',
        ]);
        Sanctum::actingAs($adminUser);

        $response = $this->post(route('api.users.store'), [
            'user_type'                  => User::ADMIN,
            'user_language'              => '::maybe_english::',
            'user_name'                  => '::user_name::',
            'user_company'               => '::localhost corporation::',
            'user_email'                 => 'email@email.com',
            'password'                   => 'longPasswordOf12345678Characters',
            'user_password_confirmation' => 'longPasswordOf12345678Characters',
        ]);

        $response->assertSuccessful();

        $response->assertJsonFragment(['name' => '::user_name::']);
        $response->assertJsonFragment(['company' => '::localhost corporation::']);
    }

    #[Group('crud')]
    public function it_returns_error_response_when_creating_a_user_without_required_fields(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        Sanctum::actingAs(User::factory()->create());

        $response = $this->post(route('api.users.store'), [
            'user_name'    => '::user_name::',
            'user_company' => '::localhost corporation::',
        ]);

        $response->assertStatus(422);

        $response->assertJsonValidationErrorFor('user_type', 'errors');
        $response->assertJsonValidationErrorFor('user_email', 'errors');
        $response->assertJsonValidationErrorFor('password', 'errors');
        $response->assertJsonValidationErrorFor('user_password_confirmation', 'errors');
    }

    #[Group('crud')]
    public function it_updates_a_user(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $initialUser = User::factory()->create([
            'user_type'     => User::ADMIN,
            'user_language' => '::maybe_english::',
            'user_name'     => '::user_name::',
            'user_company'  => '::localhost corporation::',
            'user_email'    => 'email@email.com',
            'user_tax_code' => 'ABC123456BCA',
        ]);

        $updatedData = [
            'user_name'    => '::updated_user_name::',
            'user_company' => 'Corporation of Host Local',
        ];

        Sanctum::actingAs(User::factory()->create());

        $response = $this->put(
            route('api.users.update', [
                'user' => $initialUser->user_id,
            ]),
            $updatedData
        );

        $response->assertSuccessful();

        $initialUser->refresh();

        $response->assertJsonFragment(['user_type' => User::ADMIN]);
        $response->assertJsonFragment(['user_email' => 'email@email.com']);
        $response->assertJsonFragment(['language' => 'system']);

        $response->assertJsonFragment(['name' => $updatedData['user_name']]);
        $response->assertJsonFragment(['company' => $updatedData['user_company']]);

        /*
         * #40: $initialUser is refreshed (after it was updated), so $initialUser is equal to $updatedUser
         * (it has $updatedData)
         */
        $this->assertEquals($updatedData['user_name'], $initialUser->user_name);
        $this->assertEquals($updatedData['user_company'], $initialUser->user_company);
    }

    #[Group('crud')]
    public function it_deletes_a_user(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $initialUser = User::factory()->create([
            'user_type'     => User::ADMIN,
            'user_language' => '::maybe_english::',
            'user_name'     => '::user_name::',
            'user_company'  => '::localhost corporation::',
            'user_email'    => 'email@email.com',
        ]);

        Sanctum::actingAs(User::factory()->create());

        $response = $this->deleteJson(
            route('api.users.destroy', ['user' => $initialUser->user_id])
        );

        $response->assertSuccessful();

        $getUserResponse = $this->getJson(
            route('api.users.show', [
                'user' => $initialUser->user_id,
            ])
        );
        $getUserResponse->assertNotFound();
    }
}
