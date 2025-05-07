<?php

namespace Modules\Core\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Livewire\Livewire;
use Modules\Core\Models\User;
use Modules\Core\Tests\AbstractTestCase;

class SettingsTest extends AbstractTestCase
{
    use RefreshDatabase;
    use WithFaker;
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

    // region smoke
    #[Test]
    #[Group('smoke')]
    public function it_shows_settings_index(): void
    {
        /* arrange */
        $this->markTestIncomplete();
        $this->markTestSkipped();
        $user     = User::factory()->create();
        $response = $this->actingAs(user: $user, guard: 'web')->get(route('filament.ivpl.resources.filament.resources.settings.index'));
        $response->assertSuccessful();
        $response->assertSee('general');
        $response->assertSee('invoices');
        $response->assertSee('quotes');
        $response->assertSee('taxes');
        $response->assertSee('email');
        $response->assertSee('online_payment');
        $response->assertSee('projects');
    }

    // endregion

    // region crud
    #[Test]
    #[Group('crud')]
    /**
     * @test
     *
     * @group crud
     *
     * @covers \Modules\.\Filament\./app/Filament\Resources\SettingResource
     *
     * @payload
     * []
     */
    public function it_creates_a_setting(): void
    {
        /* arrange */
        $this->markTestIncomplete();

        //$this->actingAs(User::factory()->create());

        $payload = [
        ];

        Livewire::test(CreateSetting::class)
            ->fillForm($payload)
            ->call('create')
            ->assertHasNoFormErrors();
    }

    #[Test]
    #[Group('Crud')]
    /**
     * @test
     *
     * @group crud
     *
     * @covers \Modules\.\Filament\./app/Filament\Resources\SettingResource
     *
     * @payload
     * []
     */
    public function it_fails_to_create_setting_when_required_fields_are_missing(): void
    {
        /* arrange */
        $this->markTestIncomplete();

        //$this->actingAs(User::factory()->create());

        $payload = [
        ];

        Livewire::test(CreateSetting::class)
            ->fillForm($payload)
            ->call('create')
            ->assertHasFormErrors();

        if (app()->isLocal()) {
            dump($payload);
        }
    }

    #[Test]
    #[Group('Crud')]
    /**
     * @covers \Modules\.\Filament\./app/Filament\Resources\SettingResource
     *
     * @payload
     * []
     */
    public function it_updates_a_setting(): void
    {
        /* arrange */
        $this->markTestIncomplete();
        $this->markTestIncomplete('Needs full payload and assertions.');

        //$this->actingAs(User::factory()->create());

        $record = Setting::factory()->create();

        $payload = [
        ];

        Livewire::test(EditSetting::class, ['record' => $record->getKey()])
            ->fillForm($payload)
            ->call('save')
            ->assertHasNoFormErrors();
    }

    #[Test]
    #[Group('Crud')]
    /**
     * @test
     *
     * @group crud
     *
     * @covers \Modules\.\Filament\./app/Filament\Resources\SettingResource
     *
     * @payload
     * []
     */
    public function it_fails_to_update_setting_when_required_fields_are_missing(): void
    {
        /* arrange */
        $this->markTestIncomplete();

        //$this->actingAs(User::factory()->create());

        $record = Setting::factory()->create();

        $payload = [
        ];

        Livewire::test(EditSetting::class, ['record' => $record->getKey()])
            ->fillForm($payload)
            ->call('save')
            ->assertHasFormErrors();

        if (app()->isLocal()) {
            dump($payload);
        }
    }

    #[Test]
    #[Group('Crud')]
    /**
     * @covers \Modules\.\Filament\./app/Filament\Resources\SettingResource
     *
     * @payload
     * []
     */
    public function it_deletes_a_setting(): void
    {
        /* arrange */
        $this->markTestIncomplete();
        $this->markTestIncomplete('Delete test needs confirmation logic.');

        //$this->actingAs(User::factory()->create());

        $record = Setting::factory()->create();

        Livewire::test(ListSettings::class)
            ->callTableAction('delete', $record);

        $this->assertDatabaseMissing('settings', ['id' => $record->id]);
    }

    // endregion

    // region usp

    // endregion
}
