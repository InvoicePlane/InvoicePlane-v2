<?php

namespace Modules\Core\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Livewire\Livewire;
use Modules\Core\Models\User;
use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

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
        $this->markTestIncomplete();

        /* arrange */

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
     * @payload
     * []
     */
    #[Group('crud')]
    public function it_creates_a_setting(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        //$this->actingAs(User::factory()->create());

        $payload = [
        ];

        /** act */
        $component = Livewire::actingAs($this->superAdmin())->test(CreateSetting::class)->fillForm($payload)->call('create');

        /* assert */
        $component->assertHasNoFormErrors();
    }

    #[Test]
    #[Group('Crud')]
    /**
     * @test
     *
     * @group crud
     *
     * @payload
     * []
     */
    #[Group('crud')]
    public function it_fails_to_create_setting_when_required_fields_are_missing(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        //$this->actingAs(User::factory()->create());

        $payload = [
        ];

        /** act */
        $component = Livewire::actingAs($this->superAdmin())->test(CreateSetting::class)->fillForm($payload)->call('create');

        /* assert */
        $component->assertHasFormErrors();

        if (app()->isLocal()) {
            dump($payload);
        }
    }

    #[Test]
    #[Group('Crud')]
    /**
     * @payload
     * []
     */
    #[Group('crud')]
    public function it_updates_a_setting(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $this->markTestIncomplete('Needs full payload and assertions.');

        //$this->actingAs(User::factory()->create());

        $record = Setting::factory()->create();

        $payload = [
        ];

        /** act */
        $component = Livewire::actingAs($this->superAdmin())->test(EditSetting::class, ['record' => $record->getKey()])->fillForm($payload)->call('save');

        /* assert */
        $component->assertHasNoFormErrors();
    }

    #[Test]
    #[Group('Crud')]
    /**
     * @test
     *
     * @group crud
     *
     * @payload
     * []
     */
    #[Group('crud')]
    public function it_fails_to_update_setting_when_required_fields_are_missing(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        //$this->actingAs(User::factory()->create());

        $record = Setting::factory()->create();

        $payload = [
        ];

        /** act */
        $component = Livewire::actingAs($this->superAdmin())->test(EditSetting::class, ['record' => $record->getKey()])->fillForm($payload)->call('save');

        /* assert */
        $component->assertHasFormErrors();

        if (app()->isLocal()) {
            dump($payload);
        }
    }

    #[Test]
    #[Group('Crud')]
    /**
     * @payload
     * []
     */
    #[Group('crud')]
    public function it_deletes_a_setting(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $this->markTestIncomplete('Delete test needs confirmation logic.');

        //$this->actingAs(User::factory()->create());

        $record = Setting::factory()->create();

        /** act */
        $component = Livewire::actingAs($this->superAdmin())->test(ListSettings::class)->callTableAction('delete', $record);

        $this->assertDatabaseMissing('settings', ['id' => $record->id]);
    }

    // endregion

    // region usp

    // endregion
}
