<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Livewire\Livewire;
use Modules\Core\Filament\Resources\MerchantPaymentResource\Pages\CreateMerchantPayment;
use Modules\Core\Filament\Resources\MerchantPaymentResource\Pages\EditMerchantPayment;
use Modules\Core\Filament\Resources\MerchantPaymentResource\Pages\ListMerchantPayments;
use Modules\Core\Models\MerchantPayment;
use Modules\Core\Models\User;

class MerchantPaymentsTest extends TestCase
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
     * @covers \Modules\.\Filament\./app/Filament\Resources\MerchantPaymentResource
     */
    public function it_lists_merchantpayments(): void
    {
        $this->markTestIncomplete();

        //$this->actingAs(User::factory()->create());

        Livewire::test(ListMerchantPayments::class)
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
     * @covers \Modules\.\Filament\./app/Filament\Resources\MerchantPaymentResource
     *
     * @payload
     * []
     */
    public function it_creates_a_merchantpayment(): void
    {
        $this->markTestIncomplete();

        //$this->actingAs(User::factory()->create());

        $payload = [
        ];

        Livewire::test(CreateMerchantPayment::class)
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
     * @covers \Modules\.\Filament\./app/Filament\Resources\MerchantPaymentResource
     *
     * @payload
     * []
     */
    public function it_fails_to_create_merchantpayment_when_required_fields_are_missing(): void
    {
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

    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\Group('crud')]
    /**
     * @covers \Modules\.\Filament\./app/Filament\Resources\MerchantPaymentResource
     *
     * @payload
     * []
     */
    public function it_updates_a_merchantpayment(): void
    {
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

    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\Group('crud')]
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

    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\Group('crud')]
    /**
     * @covers \Modules\.\Filament\./app/Filament\Resources\MerchantPaymentResource
     *
     * @payload
     * []
     */
    public function it_deletes_a_merchantpayment(): void
    {
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
