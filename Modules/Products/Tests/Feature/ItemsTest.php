<?php

namespace Modules\Products\Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Livewire\Livewire;
use Modules\Core\Models\Company;
use Modules\Core\Models\User;
use Modules\Core\Tests\AbstractTestCase;
use Modules\Products\Filament\Company\Resources\ItemResource;
use Modules\Products\Filament\Company\Resources\ItemResource\Pages\CreateItem;
use Modules\Products\Filament\Company\Resources\ItemResource\Pages\EditItem;
use Modules\Products\Filament\Company\Resources\ItemResource\Pages\ListItems;
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
    #[Group('smoke')]
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
            'item_name'  => 'Laptop',
        ]);

        Livewire::test(ListItems::class)
            ->assertSee('Laptop');
    }
    // endregion

    // region crud
    #[Test]
    #[Group('crud')]
    /**
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
        $this->markTestIncomplete();

        $company = Company::factory()->create();
        $user    = User::factory()->create();
        $user->companies()->attach($company->id);
        session(['current_company_id' => $company->id]);
        $this->actingAs($user);

        Livewire::test(CreateItem::class)
            ->fillForm([
                'item_name'   => 'Laptop',
                'price'       => 1000,
                'category_id' => 1,
                'unit_id'     => 1,
                'tax_rate_id' => 1,
            ])
            ->call('create')
            ->assertHasNoFormErrors();
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload
     * {
     *   "company_id": 1,
     *   "category_id": 2,
     *   "unit_id": 3,
     *   "tax_rate_id": 4,
     *   "type": "standard",
     *   "item_name": "Test Product",
     *   "price": "9.99",
     *   "cost_price": "5.00",
     *   "tariff": "TX123",
     *   "description": "Example description"
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

        Livewire::test(CreateItem::class)
            ->fillForm([
                'item_name' => '',
            ])
            ->call('create')
            ->assertHasErrors(['item_name', 'price', 'category_id']);
    }

    #[Test]
    #[Group('crud')]
    /**
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
