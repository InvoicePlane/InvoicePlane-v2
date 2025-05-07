<?php

namespace Modules\Payments\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Livewire\Livewire;
use Modules\Core\Tests\AbstractTestCase;
use Modules\Payments\Models\MerchantPayment;

//use Modules\Payments\Filament\Resources\MerchantPaymentResource\Pages\CreateMerchantPayment;
//use Modules\Payments\Filament\Resources\MerchantPaymentResource\Pages\EditMerchantPayment;
//use Modules\Payments\Filament\Resources\MerchantPaymentResource\Pages\ListMerchantPayments;

class MerchantPaymentsTest extends AbstractTestCase
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
     *
     * @covers \Modules\.\Filament\./app/Filament\Resources\MerchantPaymentResource
     */
    public function it_lists_merchantpayments(): void
    {
        /* arrange */
        $this->markTestIncomplete();

        //$this->actingAs(User::factory()->create());

        Livewire::test(ListMerchantPayments::class)
            ->assertSuccessful();
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
     * @covers \Modules\.\Filament\./app/Filament\Resources\MerchantPaymentResource
     *
     * @payload
     * []
     */
    public function it_creates_a_merchantpayment(): void
    {
        /* arrange */
        $this->markTestIncomplete();

        //$this->actingAs(User::factory()->create());

        $payload = [
        ];

        Livewire::test(CreateMerchantPayment::class)
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
     * @covers \Modules\.\Filament\./app/Filament\Resources\MerchantPaymentResource
     *
     * @payload
     * []
     */
    public function it_fails_to_create_merchantpayment_when_required_fields_are_missing(): void
    {
        /* arrange */
        $this->markTestIncomplete();

        //$this->actingAs(User::factory()->create());

        $payload = [
        ];

        Livewire::test(CreateMerchantPayment::class)
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
     * @covers \Modules\.\Filament\./app/Filament\Resources\MerchantPaymentResource
     *
     * @payload
     * []
     */
    public function it_updates_a_merchantpayment(): void
    {
        /* arrange */
        $this->markTestIncomplete();
        $this->markTestIncomplete('Needs full payload and assertions.');

        //$this->actingAs(User::factory()->create());

        $record = MerchantPayment::factory()->create();

        $payload = [
        ];

        Livewire::test(EditMerchantPayment::class, ['record' => $record->getKey()])
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
     * @covers \Modules\.\Filament\./app/Filament\Resources\MerchantPaymentResource
     *
     * @payload
     * []
     */
    public function it_fails_to_update_merchantpayment_when_required_fields_are_missing(): void
    {
        /* arrange */
        $this->markTestIncomplete();

        //$this->actingAs(User::factory()->create());

        $record = MerchantPayment::factory()->create();

        $payload = [
        ];

        Livewire::test(EditMerchantPayment::class, ['record' => $record->getKey()])
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
     * @covers \Modules\.\Filament\./app/Filament\Resources\MerchantPaymentResource
     *
     * @payload
     * []
     */
    public function it_deletes_a_merchantpayment(): void
    {
        /* arrange */
        $this->markTestIncomplete();
        $this->markTestIncomplete('Delete test needs confirmation logic.');

        //$this->actingAs(User::factory()->create());

        $record = MerchantPayment::factory()->create();

        Livewire::test(ListMerchantPayments::class)
            ->callTableAction('delete', $record);

        $this->assertDatabaseMissing('merchantpayments', ['id' => $record->id]);
    }

    // endregion

    // region usp

    // endregion
}
