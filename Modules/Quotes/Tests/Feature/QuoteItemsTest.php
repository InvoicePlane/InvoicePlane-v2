<?php

namespace Modules\Quotes\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Livewire\Livewire;
use Modules\Core\Tests\AbstractTestCase;
use Modules\Quotes\Models\QuoteItem;

//use Modules\Core\Filament\Resources\QuoteItemResource\Pages\CreateQuoteItem;
//use Modules\Core\Filament\Resources\QuoteItemResource\Pages\EditQuoteItem;
//use Modules\Core\Filament\Resources\QuoteItemResource\Pages\ListQuoteItems;

class QuoteItemsTest extends AbstractTestCase
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
    public function it_lists_quote_items(): void
    {
        /* arrange */
        $this->markTestIncomplete();

        //$this->actingAs(User::factory()->create());

        Livewire::test(ListQuoteItems::class)
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
     * @covers \Modules\.\Filament\./app/Filament\Resources\QuoteItemResource
     *
     * @payload
     * []
     */
    public function it_creates_a_quoteitem(): void
    {
        /* arrange */
        $this->markTestIncomplete();

        //$this->actingAs(User::factory()->create());

        $payload = [
        ];

        Livewire::test(CreateQuoteItem::class)
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
     * @covers \Modules\.\Filament\./app/Filament\Resources\QuoteItemResource
     *
     * @payload
     * []
     */
    public function it_fails_to_create_quoteitem_when_required_fields_are_missing(): void
    {
        /* arrange */
        $this->markTestIncomplete();

        //$this->actingAs(User::factory()->create());

        $payload = [
        ];

        Livewire::test(CreateQuoteItem::class)
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
     * @covers \Modules\.\Filament\./app/Filament\Resources\QuoteItemResource
     *
     * @payload
     * []
     */
    public function it_updates_a_quoteitem(): void
    {
        /* arrange */
        $this->markTestIncomplete();
        $this->markTestIncomplete('Needs full payload and assertions.');

        //$this->actingAs(User::factory()->create());

        $record = QuoteItem::factory()->create();

        $payload = [
        ];

        Livewire::test(EditQuoteItem::class, ['record' => $record->getKey()])
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
     * @covers \Modules\.\Filament\./app/Filament\Resources\QuoteItemResource
     *
     * @payload
     * []
     */
    public function it_fails_to_update_quoteitem_when_required_fields_are_missing(): void
    {
        /* arrange */
        $this->markTestIncomplete();

        //$this->actingAs(User::factory()->create());

        $record = QuoteItem::factory()->create();

        $payload = [
        ];

        Livewire::test(EditQuoteItem::class, ['record' => $record->getKey()])
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
     * @covers \Modules\.\Filament\./app/Filament\Resources\QuoteItemResource
     *
     * @payload
     * []
     */
    public function it_deletes_a_quoteitem(): void
    {
        /* arrange */
        $this->markTestIncomplete();
        $this->markTestIncomplete('Delete test needs confirmation logic.');

        //$this->actingAs(User::factory()->create());

        $record = QuoteItem::factory()->create();

        Livewire::test(ListQuoteItems::class)
            ->callTableAction('delete', $record);

        $this->assertDatabaseMissing('quote_items', ['id' => $record->id]);
    }

    // endregion

    // region usp

    // endregion
}
