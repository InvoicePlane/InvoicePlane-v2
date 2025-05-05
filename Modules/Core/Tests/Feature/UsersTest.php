<?php

namespace Modules\Core\Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Livewire\Livewire;
use Modules\Core\Filament\Admin\Resources\UserResource;
use Modules\Core\Filament\Admin\Resources\UserResource\Pages\CreateUser;
use Modules\Core\Filament\Admin\Resources\UserResource\Pages\EditUser;
use Modules\Core\Filament\Admin\Resources\UserResource\Pages\ListUsers;
use Modules\Core\Models\User;
use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(UserResource::class)]

class UsersTest extends AbstractTestCase
{
    use WithFaker;
    use WithoutMiddleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
    }

    // region smoke
    #[Test]
    #[Group('smoke')]
    /**
     * \Modules\Core\Filament\Admin\Resources\UserResource.
     *
     * @payload
     * {
     * "name": "Example",
     * "email": "Example",
     * "email_verified_at": "2025-04-30",
     * "password": "Example",
     * "remember_token": "Example"
     * }
     */
    public function it_creates_a_user(): void
    {
        $this->markTestIncomplete();

        //$this->actingAs(User::factory()->create());

        $payload = [
            'name'              => 'Example',
            'email'             => 'Example',
            'email_verified_at' => '2025-04-30',
            'password'          => 'Example',
            'remember_token'    => 'Example',
        ];

        Livewire::test(CreateUser::class)
            ->fillForm($payload)
            ->call('create')
            ->assertHasNoFormErrors();
    }

    #[Test]
    #[Group('crud')]
    /**
     * \Modules\Core\Filament\Admin\Resources\UserResource.
     *
     * @payload
     * {
     * "name": "Example",
     * "email": "Example",
     * "email_verified_at": "2025-04-30",
     * "password": "Example",
     * "remember_token": "Example"
     * }
     */
    public function it_updates_a_user(): void
    {
        $this->markTestIncomplete('Needs full payload and assertions.');

        //$this->actingAs(User::factory()->create());

        $record = User::factory()->create();

        $payload = [
            'name'              => 'Example',
            'email'             => 'Example',
            'email_verified_at' => '2025-04-30',
            'password'          => 'Example',
            'remember_token'    => 'Example',
        ];

        Livewire::test(EditUser::class, ['record' => $record->getKey()])
            ->fillForm($payload)
            ->call('save')
            ->assertHasNoFormErrors();
    }

    #[Test]
    #[Group('crud')]
    /**
     * \Modules\Core\Filament\Admin\Resources\UserResource.
     *
     * @payload
     * {
     * "name": "Example",
     * "email": "Example",
     * "email_verified_at": "2025-04-30",
     * "password": "Example",
     * "remember_token": "Example"
     * }
     */
    public function it_deletes_a_user(): void
    {
        $this->markTestIncomplete('Delete test needs confirmation logic.');

        //$this->actingAs(User::factory()->create());

        $record = User::factory()->create();

        Livewire::test(ListUsers::class)
            ->callTableAction('delete', $record);

        $this->assertDatabaseMissing('users', ['id' => $record->id]);
    }

    // endregion

    // region usp

    // endregion
}
