<?php

namespace Modules\Products\Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use League\CommonMark\Extension\CommonMark\Node\Block\ListItem;
use Livewire\Livewire;
use Modules\Core\Models\Company;
use Modules\Core\Models\User;
use Modules\Core\Tests\AbstractTestCase;
use Modules\Products\Filament\Company\Resources\ItemResource;
use Modules\Products\Filament\Company\Resources\ItemResource\Pages\CreateItem;
use Modules\Products\Filament\Company\Resources\ItemResource\Pages\EditItem;
use Modules\Products\Models\Item;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(ItemResource::class)]
class ItemsTest extends AbstractTestCase
{
    use WithFaker;
    use WithoutMiddleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
    }

    // region smoke
    #[Test]
    #[Group('crud')]
    public function it_lists_items(): void
    {
        $this->markTestIncomplete();

        $company = Company::factory()->create();
        $user    = User::factory()->create();
        $user->companies()->attach($company->id);
        session(['current_company_id' => $company->id]);
        $this->actingAs($user);

        Item::factory()->create([
            'company_id' => $company->id,
            'item_name'  => 'Test Product',
        ]);

        Livewire::test(ListItem::class)
            ->assertSee('Test Product');
    }
    // endregion

    // region crud
    #[Test]
    #[Group('crud')]
    /**
     * @test
     *
     * @payload
     * {
     *   "company_id": 1,
     *   "category_id": 2,
     *   "unit_id": 3,
     *   "tax_rate_id": 4,
     *   "type": "standard",
     *   "code": "P001",
     *   "item_name": "Test Product",
     *   "price": "9.99",
     *   "cost_price": "5.00",
     *   "tariff": "TX123",
     *   "description": "Example description"
     * }
     */
    public function it_creates_a_product(): void
    {
        $company = Company::factory()->create();
        $user    = User::factory()->create();
        $user->companies()->attach($company->id);
        session(['current_company_id' => $company->id]);
        $this->actingAs($user);

        $payload = [
            'company_id'  => $company->id,
            'category_id' => 2,
            'unit_id'     => 3,
            'tax_rate_id' => 4,
            'type'        => 'standard',
            'code'        => 'P001',
            'item_name'   => 'Test Product',
            'price'       => 9.99,
            'cost_price'  => 5.00,
            'tariff'      => 'TX123',
            'description' => 'Example description',
        ];

        Livewire::test(CreateItem::class)
            ->fillForm($payload)
            ->call('create')
            ->assertHasNoFormErrors();
    }

    #[Test]
    #[Group('module')]
    /**
     * @test
     *
     * @payload
     * {
     *   "company_id": 1,
     *   "item_name": "Missing Code"
     * }
     */
    public function it_fails_to_create_product_without_code(): void
    {
        $this->markTestIncomplete();

        $company = Company::factory()->create();
        $user    = User::factory()->create();
        $user->companies()->attach($company->id);
        session(['current_company_id' => $company->id]);
        $this->actingAs($user);

        $payload = [
            'company_id' => $company->id,
            'item_name'  => 'Missing Code',
        ];

        Livewire::test(CreateItem::class)
            ->fillForm($payload)
            ->call('create')
            ->assertHasFormErrors(['code' => 'required']);
    }

    #[Test]
    #[Group('crud')]
    /**
     * \Modules\Products\Filament\Company\Resources\ItemResource.
     *
     * @payload
     * {
     * "company_id": "Value",
     * "category_id": "Value",
     * "unit_id": "Value",
     * "tax_rate_id": "Value",
     * "type": "Value",
     * "code": "Example",
     * "item_name": "Example",
     * "price": "9.99",
     * "cost_price": "9.99",
     * "tariff": "Example",
     * "description": "Example"
     * }
     */
    public function it_fails_to_update_item_when_required_fields_are_missing(): void
    {
        $this->markTestIncomplete();

        //$this->actingAs(User::factory()->create());

        $record = Item::factory()->create();

        $payload = [
            'company_id'  => 'Value',
            'category_id' => 'Value',
            'unit_id'     => 'Value',
            'tax_rate_id' => 'Value',
            'type'        => 'Value',
            'code'        => 'Example',
            'item_name'   => 'Example',
            'price'       => 9.99,
            'cost_price'  => 9.99,
            'tariff'      => 'Example',
            'description' => 'Example',
        ];

        Livewire::test(EditItem::class, ['record' => $record->getKey()])
            ->fillForm($payload)
            ->call('save')
            ->assertHasFormErrors();

        if (app()->isLocal()) {
            dump($payload);
        }
    }
    // endregion
}
