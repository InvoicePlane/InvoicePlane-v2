<?php

namespace Tests\Feature;

use App\Filament\Resources\ExpenseVendorResource\Pages\CreateExpenseVendor;
use App\Filament\Resources\ExpenseVendorResource\Pages\EditExpenseVendor;
use App\Filament\Resources\ExpenseVendorResource\Pages\ListExpenseVendors;
use App\Models\ExpenseVendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Livewire\Livewire;
use Modules\Core\Models\User;
use Tests\TestCase;

class ExpenseVendorsTest extends TestCase
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
     * @covers \Modules\.\Filament\./app/Filament\Resources\ExpenseVendorResource
     */
    public function it_lists_expensevendors(): void
    {
        $this->markTestIncomplete();

        //$this->actingAs(User::factory()->create());

        Livewire::test(ListExpenseVendors::class)
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
     * @covers \Modules\.\Filament\./app/Filament\Resources\ExpenseVendorResource
     *
     * @payload
     * []
     */
    public function it_creates_a_expensevendor(): void
    {
        $this->markTestIncomplete();

        //$this->actingAs(User::factory()->create());

        $payload = [
        ];

        Livewire::test(CreateExpenseVendor::class)
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
     * @covers \Modules\.\Filament\./app/Filament\Resources\ExpenseVendorResource
     *
     * @payload
     * []
     */
    public function it_fails_to_create_expensevendor_when_required_fields_are_missing(): void
    {
        $this->markTestIncomplete();

        //$this->actingAs(User::factory()->create());

        $payload = [
        ];

        Livewire::test(CreateExpenseVendor::class)
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
     * @covers \Modules\.\Filament\./app/Filament\Resources\ExpenseVendorResource
     *
     * @payload
     * []
     */
    public function it_updates_a_expensevendor(): void
    {
        $this->markTestIncomplete('Needs full payload and assertions.');

        //$this->actingAs(User::factory()->create());

        $record = ExpenseVendor::factory()->create();

        $payload = [
        ];

        Livewire::test(EditExpenseVendor::class, ['record' => $record->getKey()])
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
     * @covers \Modules\.\Filament\./app/Filament\Resources\ExpenseVendorResource
     *
     * @payload
     * []
     */
    public function it_fails_to_update_expensevendor_when_required_fields_are_missing(): void
    {
        $this->markTestIncomplete();

        //$this->actingAs(User::factory()->create());

        $record = ExpenseVendor::factory()->create();

        $payload = [
        ];

        Livewire::test(EditExpenseVendor::class, ['record' => $record->getKey()])
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
     * @covers \Modules\.\Filament\./app/Filament\Resources\ExpenseVendorResource
     *
     * @payload
     * []
     */
    public function it_deletes_a_expensevendor(): void
    {
        $this->markTestIncomplete('Delete test needs confirmation logic.');

        //$this->actingAs(User::factory()->create());

        $record = ExpenseVendor::factory()->create();

        Livewire::test(ListExpenseVendors::class)
            ->callTableAction('delete', $record);

        $this->assertDatabaseMissing('expensevendors', ['id' => $record->id]);
    }

    // endregion

    // region usp

    // endregion
}
