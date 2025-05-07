<?php

namespace Modules\Core\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Livewire\Livewire;
use Modules\Core\Tests\AbstractTestCase;

//use Modules\Core\Models\Currency;
//use Modules\Core\Filament\Resources\CurrencyResource\Pages\CreateCurrency;
//use Modules\Core\Filament\Resources\CurrencyResource\Pages\EditCurrency;
//use Modules\Core\Filament\Resources\CurrencyResource\Pages\ListCurrencies;

class CurrenciesTest extends AbstractTestCase
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
     * @covers \Modules\.\Filament\./app/Filament\Resources\CurrencyResource
     */
    public function it_lists_currencies(): void
    {
        $this->markTestIncomplete();

        //$this->actingAs(User::factory()->create());

        Livewire::test(ListCurrencies::class)
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
     * @covers \Modules\.\Filament\./app/Filament\Resources\CurrencyResource
     *
     * @payload
     * []
     */
    public function it_creates_a_currency(): void
    {
        $this->markTestIncomplete();

        //$this->actingAs(User::factory()->create());

        $payload = [
        ];

        Livewire::test(CreateCurrency::class)
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
     * @covers \Modules\.\Filament\./app/Filament\Resources\CurrencyResource
     *
     * @payload
     * []
     */
    public function it_fails_to_create_currency_when_required_fields_are_missing(): void
    {
        $this->markTestIncomplete();

        //$this->actingAs(User::factory()->create());

        $payload = [
        ];

        Livewire::test(CreateCurrency::class)
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
     * @covers \Modules\.\Filament\./app/Filament\Resources\CurrencyResource
     *
     * @payload
     * []
     */
    public function it_updates_a_currency(): void
    {
        $this->markTestIncomplete('Needs full payload and assertions.');

        //$this->actingAs(User::factory()->create());

        $record = Currency::factory()->create();

        $payload = [
        ];

        Livewire::test(EditCurrency::class, ['record' => $record->getKey()])
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
     * @covers \Modules\.\Filament\./app/Filament\Resources\CurrencyResource
     *
     * @payload
     * []
     */
    public function it_fails_to_update_currency_when_required_fields_are_missing(): void
    {
        $this->markTestIncomplete();

        //$this->actingAs(User::factory()->create());

        $record = Currency::factory()->create();

        $payload = [
        ];

        Livewire::test(EditCurrency::class, ['record' => $record->getKey()])
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
     * @covers \Modules\.\Filament\./app/Filament\Resources\CurrencyResource
     *
     * @payload
     * []
     */
    public function it_deletes_a_currency(): void
    {
        $this->markTestIncomplete('Delete test needs confirmation logic.');

        //$this->actingAs(User::factory()->create());

        $record = Currency::factory()->create();

        Livewire::test(ListCurrencies::class)
            ->callTableAction('delete', $record);

        $this->assertDatabaseMissing('currencies', ['id' => $record->id]);
    }

    // endregion

    // region usp

    // endregion
}
