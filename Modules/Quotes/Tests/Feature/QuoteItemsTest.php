<?php

namespace Tests\Feature;

use Modules\Core\Tests\AbstractTestCase;

use Modules\Core\Support\Results\Quotes;

use Modules\Core\Models\User;

use Modules\Quotes\Models\QuoteItem;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Livewire\Livewire;
use Modules\Core\Filament\Resources\QuoteItemResource\Pages\CreateQuoteItem;
use Modules\Core\Filament\Resources\QuoteItemResource\Pages\EditQuoteItem;
use Modules\Core\Filament\Resources\QuoteItemResource\Pages\ListQuoteItems;
use Modules\Core\Models\QuoteItem;

class QuoteItemsTest extends TestCase
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
     * @covers \Modules\.\Filament\./app/Filament\Resources\QuoteItemResource
     */
    public function it_lists_quoteitems(): void
    {
        $this->markTestIncomplete();

        //$this->actingAs(User::factory()->create());

        Livewire::test(ListQuoteItems::class)
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
     * @covers \Modules\.\Filament\./app/Filament\Resources\QuoteItemResource
     *
     * @payload
     * []
     */
    public function it_creates_a_quoteitem(): void
    {
        $this->markTestIncomplete();

        //$this->actingAs(User::factory()->create());

        $payload = [
        ];

        Livewire::test(CreateQuoteItem::class)
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
     * @covers \Modules\.\Filament\./app/Filament\Resources\QuoteItemResource
     *
     * @payload
     * []
     */
    public function it_fails_to_create_quoteitem_when_required_fields_are_missing(): void
    {
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

    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\Group('crud')]
    /**
     * @covers \Modules\.\Filament\./app/Filament\Resources\QuoteItemResource
     *
     * @payload
     * []
     */
    public function it_updates_a_quoteitem(): void
    {
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

    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\Group('crud')]
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

    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\Group('crud')]
    /**
     * @covers \Modules\.\Filament\./app/Filament\Resources\QuoteItemResource
     *
     * @payload
     * []
     */
    public function it_deletes_a_quoteitem(): void
    {
        $this->markTestIncomplete('Delete test needs confirmation logic.');

        //$this->actingAs(User::factory()->create());

        $record = QuoteItem::factory()->create();

        Livewire::test(ListQuoteItems::class)
            ->callTableAction('delete', $record);

        $this->assertDatabaseMissing('quoteitems', ['id' => $record->id]);
    }

    // endregion

    // region usp

    // endregion
}
