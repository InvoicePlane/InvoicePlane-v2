<?php

namespace Modules\Payments\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Livewire\Livewire;
use Modules\Core\Tests\AbstractTestCase;

//use Modules\Core\Filament\Resources\MerchantClientResource\Pages\CreateMerchantClient;
//use Modules\Core\Filament\Resources\MerchantClientResource\Pages\EditMerchantClient;
//use Modules\Core\Filament\Resources\MerchantClientResource\Pages\ListMerchantClients;

class MerchantClientsTest extends AbstractTestCase
{
    use RefreshDatabase;
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
     * @group smoke
     */
    public function it_lists_merchant_clients(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        //$this->actingAs(User::factory()->create());

        /** act */
        $component = Livewire::actingAs($this->user)->test(ListMerchantClients::class);

        /* assert */
        $component->assertSuccessful();
    }

    // endregion

    // region crud
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
    public function it_creates_a_merchant_client(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        //$this->actingAs(User::factory()->create());

        $payload = [
        ];

        /** act */
        $component = Livewire::actingAs($this->user)->test(CreateMerchantClient::class)->fillForm($payload)->call('create');

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
    public function it_fails_to_create_merchant_client_when_required_fields_are_missing(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        //$this->actingAs(User::factory()->create());

        $payload = [
        ];

        /** act */
        $component = Livewire::actingAs($this->user)->test(CreateMerchantClient::class)->fillForm($payload)->call('create');

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
    public function it_updates_a_merchant_client(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $this->markTestIncomplete('Needs full payload and assertions.');

        //$this->actingAs(User::factory()->create());

        $record = MerchantRelation::factory()->create();

        $payload = [
        ];

        /** act */
        $component = Livewire::actingAs($this->user)->test(EditMerchantClient::class, ['record' => $record->getKey()])->fillForm($payload)->call('save');

        /* assert */
        $component->assertHasNoFormErrors();
    }

    #[Test]
    #[Group('Crud')]
    /**
     * @payload
     * []
     */
    public function it_fails_to_update_merchant_client_when_required_fields_are_missing(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        //$this->actingAs(User::factory()->create());

        $record = MerchantRelation::factory()->create();

        $payload = [
        ];

        /** act */
        $component = Livewire::actingAs($this->user)->test(EditMerchantClient::class, ['record' => $record->getKey()])->fillForm($payload)->call('save');

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
    public function it_deletes_a_merchant_client(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $this->markTestIncomplete('Delete test needs confirmation logic.');

        //$this->actingAs(User::factory()->create());

        $record = MerchantRelation::factory()->create();

        /** act */
        $component = Livewire::actingAs($this->user)->test(ListMerchantClients::class)->callTableAction('delete', $record);

        $this->assertDatabaseMissing('merchant_clients', ['id' => $record->id]);
    }

    // endregion

    // region usp

    // endregion
}
