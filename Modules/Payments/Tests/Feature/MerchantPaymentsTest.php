<?php

namespace Modules\Payments\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Livewire\Livewire;
use Modules\Core\Tests\AbstractTestCase;
use Modules\Payments\Models\MerchantPayment;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

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
     */
    #[Group('crud')]
    public function it_lists_merchantpayments(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        //$this->actingAs(User::factory()->create());

        /** act */
        $component = Livewire::actingAs($this->user)->test(ListMerchantPayments::class);

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
    #[Group('crud')]
    public function it_creates_a_merchantpayment(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        //$this->actingAs(User::factory()->create());

        $payload = [
        ];

        /** act */
        $component = Livewire::actingAs($this->user)->test(CreateMerchantPayment::class)->fillForm($payload)->call('create');

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
    public function it_fails_to_create_merchantpayment_when_required_fields_are_missing(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        //$this->actingAs(User::factory()->create());

        $payload = [
        ];

        /** act */
        $component = Livewire::actingAs($this->user)->test(CreateMerchantPayment::class)->fillForm($payload)->call('create');

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
    public function it_updates_a_merchantpayment(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $this->markTestIncomplete('Needs full payload and assertions.');

        //$this->actingAs(User::factory()->create());

        $record = MerchantPayment::factory()->create();

        $payload = [
        ];

        /** act */
        $component = Livewire::actingAs($this->user)->test(EditMerchantPayment::class, ['record' => $record->getKey()])->fillForm($payload)->call('save');

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
    public function it_fails_to_update_merchantpayment_when_required_fields_are_missing(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        //$this->actingAs(User::factory()->create());

        $record = MerchantPayment::factory()->create();

        $payload = [
        ];

        /** act */
        $component = Livewire::actingAs($this->user)->test(EditMerchantPayment::class, ['record' => $record->getKey()])->fillForm($payload)->call('save');

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
    public function it_deletes_a_merchantpayment(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $this->markTestIncomplete('Delete test needs confirmation logic.');

        //$this->actingAs(User::factory()->create());

        $record = MerchantPayment::factory()->create();

        /** act */
        $component = Livewire::actingAs($this->user)->test(ListMerchantPayments::class)->callTableAction('delete', $record);

        $this->assertDatabaseMissing('merchantpayments', ['id' => $record->id]);
    }

    // endregion

    // region usp

    // endregion
}
