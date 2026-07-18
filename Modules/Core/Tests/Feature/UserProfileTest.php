<?php

namespace Modules\Core\Tests\Feature;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Core\Filament\Company\Pages\Auth\EditProfile;
use Modules\Core\Filament\Company\Pages\MyCompanies;
use Modules\Core\Models\Company;
use Modules\Core\Services\UserService;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(EditProfile::class)]
#[CoversClass(MyCompanies::class)]
class UserProfileTest extends AbstractCompanyPanelTestCase
{
    #[Test]
    public function it_saves_the_user_data_form(): void
    {
        /* Act */
        $this->testLivewire(EditProfile::class)
            ->fillForm(['name' => 'Jane Doe'])
            ->call('save')
            /* Assert */
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('users', [
            'id'   => $this->user->id,
            'name' => 'Jane Doe',
        ]);
    }

    #[Test]
    public function it_updates_the_users_language(): void
    {
        /* Act */
        $this->testLivewire(EditProfile::class)
            ->fillForm(['language' => 'fr'])
            ->call('save')
            /* Assert */
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('users', [
            'id'       => $this->user->id,
            'language' => 'fr',
        ]);
    }

    #[Test]
    public function it_removes_the_upload_and_stored_file_when_avatar_is_cleared(): void
    {
        /* Arrange */
        Storage::fake('public');
        Storage::disk('public')->put('avatars/avatar.png', 'contents');
        app(UserService::class)->updateAvatar($this->user, 'avatars/avatar.png');

        /* Act */
        $this->testLivewire(EditProfile::class)
            ->fillForm(['avatar' => null])
            ->call('save')
            /* Assert */
            ->assertHasNoFormErrors();

        Storage::disk('public')->assertMissing('avatars/avatar.png');
        $this->assertDatabaseMissing('uploads', [
            'uploadable_type'  => $this->user::class,
            'uploadable_id'    => $this->user->id,
            'file_description' => 'avatar',
        ]);
    }

    #[Test]
    public function it_requires_matching_confirmation_for_password_change(): void
    {
        /* Act & Assert */
        $this->testLivewire(EditProfile::class)
            ->fillForm([
                'password'              => 'NewSecure!1',
                'password_confirmation' => 'wrong',
            ])
            ->call('save')
            ->assertHasFormErrors(['password']);

        $this->assertFalse(Hash::check('NewSecure!1', $this->user->fresh()->password));
    }

    #[Test]
    public function it_hashes_the_password_when_changed(): void
    {
        /* Act */
        $this->testLivewire(EditProfile::class)
            ->fillForm([
                'password'              => 'NewSecure!1',
                'password_confirmation' => 'NewSecure!1',
            ])
            ->call('save')
            /* Assert */
            ->assertHasNoFormErrors();

        $this->assertTrue(Hash::check('NewSecure!1', $this->user->fresh()->password));
    }

    #[Test]
    public function it_renders_the_company_list_for_the_authenticated_user(): void
    {
        /* Act & Assert */
        $this->testLivewire(MyCompanies::class)
            ->assertSuccessful()
            ->assertSee($this->company->name)
            ->assertSee($this->company->search_code);
    }

    #[Test]
    public function it_sets_the_tenant_and_redirects_to_the_target_dashboard_when_switching(): void
    {
        /* Arrange */
        $otherCompany = Company::factory()->create(['search_code' => 'OTHERCO']);
        $this->user->companies()->attach($otherCompany);

        /* Act */
        $component = $this->testLivewire(MyCompanies::class)
            ->callTableAction('switch', $otherCompany);

        /* Assert */
        $component->assertRedirect(route('filament.company.pages.dashboard', [
            'tenant' => Str::lower($otherCompany->search_code),
        ]));

        $this->assertSame($otherCompany->id, session('current_company_id'));
    }
}
