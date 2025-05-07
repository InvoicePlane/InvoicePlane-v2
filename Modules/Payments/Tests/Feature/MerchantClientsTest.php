<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Livewire\Livewire;
use Modules\Core\Filament\Resources\MerchantClientResource\Pages\CreateMerchantClient;
use Modules\Core\Filament\Resources\MerchantClientResource\Pages\EditMerchantClient;
use Modules\Core\Filament\Resources\MerchantClientResource\Pages\ListMerchantClients;
use Modules\Core\Models\MerchantClient;
use Modules\Core\Tests\AbstractTestCase;

class MerchantClientsTest extends TestCase
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
    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\Group('smoke')]
    /**
     * @group smoke
     *
     * @covers \Modules\.\Filament\./app/Filament\Resources\MerchantClientResource
     */
    public function it_lists_merchantclients(): void
    {
        $this->markTestIncomplete();

        //$this->actingAs(User::factory()->create());

        Livewire::test(ListMerchantClients::class)
            ->assertSuccessful();
    }

    // endregion

    // region crud
    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\Group('crud')]
    /**
     * @test
     *
     * @group crud
     *
     * @covers \Modules\.\Filament\./app/Filament\Resources\MerchantClientResource
     *
     * @payload
     * []
     */
    public function it_creates_a_merchantclient(): void
    {
        $this->markTestIncomplete();

        //$this->actingAs(User::factory()->create());

        $payload = [
        ];

        Livewire::test(CreateMerchantClient::class)
            ->fillForm($payload)
            ->call('create')
            ->assertHasNoFormErrors();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\Group('crud')]
    /**
     * @test
     *
     * @group crud
     *
     * @covers \Modules\.\Filament\./app/Filament\Resources\MerchantClientResource
     *
     * @payload
     * []
     */
    public function it_fails_to_create_merchantclient_when_required_fields_are_missing(): void
    {
        $this->markTestIncomplete();

        //$this->actingAs(User::factory()->create());

        $payload = [
        ];

        Livewire::test(CreateMerchantClient::class)
            ->fillForm($payload)
            ->call('create')
            ->assertHasFormErrors();

        if (app()->isLocal()) {
            dump($payload);
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\Group('crud')]
    /**
     * @covers \Modules\.\Filament\./app/Filament\Resources\MerchantClientResource
     *
     * @payload
     * []
     */
    public function it_updates_a_merchantclient(): void
    {
        $this->markTestIncomplete('Needs full payload and assertions.');

        //$this->actingAs(User::factory()->create());

        $record = MerchantClient::factory()->create();

        $payload = [
        ];

        Livewire::test(EditMerchantClient::class, ['record' => $record->getKey()])
            ->fillForm($payload)
            ->call('save')
            ->assertHasNoFormErrors();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\Group('crud')]
    /**
     * @test
     *
     * @group crud
     *
     * @covers \Modules\.\Filament\./app/Filament\Resources\MerchantClientResource
     *
     * @payload
     * []
     */
    public function it_fails_to_update_merchantclient_when_required_fields_are_missing(): void
    {
        $this->markTestIncomplete();

        //$this->actingAs(User::factory()->create());

        $record = MerchantClient::factory()->create();

        $payload = [
        ];

        Livewire::test(EditMerchantClient::class, ['record' => $record->getKey()])
            ->fillForm($payload)
            ->call('save')
            ->assertHasFormErrors();

        if (app()->isLocal()) {
            dump($payload);
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\Group('crud')]
    /**
     * @covers \Modules\.\Filament\./app/Filament\Resources\MerchantClientResource
     *
     * @payload
     * []
     */
    public function it_deletes_a_merchantclient(): void
    {
        $this->markTestIncomplete('Delete test needs confirmation logic.');

        //$this->actingAs(User::factory()->create());

        $record = MerchantClient::factory()->create();

        Livewire::test(ListMerchantClients::class)
            ->callTableAction('delete', $record);

        $this->assertDatabaseMissing('merchantclients', ['id' => $record->id]);
    }

    // endregion

    // region usp

    // endregion
}
