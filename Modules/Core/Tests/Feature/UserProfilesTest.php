<?php

namespace Modules\Core\Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Livewire\Livewire;
use Modules\Core\Filament\Admin\Resources\UserProfileResource;
use Modules\Core\Filament\Admin\Resources\UserProfileResource\Pages\CreateUserProfile;
use Modules\Core\Filament\Admin\Resources\UserProfileResource\Pages\EditUserProfile;
use Modules\Core\Filament\Admin\Resources\UserProfileResource\Pages\ListUserProfiles;
use Modules\Core\Models\UserProfile;
use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(UserProfileResource::class)]

class UserProfilesTest extends AbstractTestCase
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
     * \Modules\Core\Filament\Admin\Resources\UserProfileResource.
     *
     * @payload
     * {
     * "user_id": "Value",
     * "user_phone": "Example",
     * "user_mobile": "Example",
     * "user_language": "Example",
     * "user_web": "Example",
     * "user_vat_id": "Value",
     * "user_tax_code": "Example",
     * "user_iban": "Example"
     * }
     */
    public function it_creates_a_userprofile(): void
    {
        $this->markTestIncomplete();

        //$this->actingAs(User::factory()->create());

        $payload = [
            'user_id'       => 'Value',
            'user_phone'    => 'Example',
            'user_mobile'   => 'Example',
            'user_language' => 'Example',
            'user_web'      => 'Example',
            'user_vat_id'   => 'Value',
            'user_tax_code' => 'Example',
            'user_iban'     => 'Example',
        ];

        Livewire::test(CreateUserProfile::class)
            ->fillForm($payload)
            ->call('create')
            ->assertHasNoFormErrors();
    }

    #[Test]
    #[Group('crud')]
    /**
     * \Modules\Core\Filament\Admin\Resources\UserProfileResource.
     *
     * @payload
     * {
     * "user_id": "Value",
     * "user_phone": "Example",
     * "user_mobile": "Example",
     * "user_language": "Example",
     * "user_web": "Example",
     * "user_vat_id": "Value",
     * "user_tax_code": "Example",
     * "user_iban": "Example"
     * }
     */
    public function it_updates_a_userprofile(): void
    {
        $this->markTestIncomplete('Needs full payload and assertions.');

        //$this->actingAs(User::factory()->create());

        $record = UserProfile::factory()->create();

        $payload = [
            'user_id'       => 'Value',
            'user_phone'    => 'Example',
            'user_mobile'   => 'Example',
            'user_language' => 'Example',
            'user_web'      => 'Example',
            'user_vat_id'   => 'Value',
            'user_tax_code' => 'Example',
            'user_iban'     => 'Example',
        ];

        Livewire::test(EditUserProfile::class, ['record' => $record->getKey()])
            ->fillForm($payload)
            ->call('save')
            ->assertHasNoFormErrors();
    }

    #[Test]
    #[Group('crud')]
    /**
     * \Modules\Core\Filament\Admin\Resources\UserProfileResource.
     *
     * @payload
     * {
     * "user_id": "Value",
     * "user_phone": "Example",
     * "user_mobile": "Example",
     * "user_language": "Example",
     * "user_web": "Example",
     * "user_vat_id": "Value",
     * "user_tax_code": "Example",
     * "user_iban": "Example"
     * }
     */
    public function it_deletes_a_userprofile(): void
    {
        $this->markTestIncomplete('Delete test needs confirmation logic.');

        //$this->actingAs(User::factory()->create());

        $record = UserProfile::factory()->create();

        Livewire::test(ListUserProfiles::class)
            ->callTableAction('delete', $record);

        $this->assertDatabaseMissing('userprofiles', ['id' => $record->id]);
    }

    // endregion

    // region usp

    // endregion
}
