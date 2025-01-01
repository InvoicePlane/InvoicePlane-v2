<?php

namespace Modules\Products\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\Core\Models\User;
use Modules\Core\tests\AbstractTestCase;
use Modules\Products\Filament\Resources\ProductFamilyResource\Pages\CreateProductFamily;
use Modules\Products\Filament\Resources\ProductFamilyResource\Pages\EditProductFamily;
use Modules\Products\Filament\Resources\ProductFamilyResource\Pages\ManageProductFamilies;
use Modules\Products\Models\ProductFamily;

class ProductFamiliesTest extends AbstractTestCase
{
    use RefreshDatabase;

    // region CRUD Tests

    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function it_shows_product_families_index(): void
    {
        // $this->authenticated();

        $user = User::factory()->create();
        ProductFamily::factory()->create([
            'family_name' => '::product_family_name::',
        ]);

        Livewire::test(ManageProductFamilies::class)
            ->assertSee('::product_family_name::');
    }

    /**
     * @test
     * Payload:
     * {
     * "family_name": "::product_family_name::"
     * }
     *
     * @skip Not implemented yet
     */
    public function it_creates_a_product_family(): void
    {
        $this->markTestSkipped('something about a view');
        $payload = [
            'family_name' => '::product_family_name::',
        ];

        Livewire::test(CreateProductFamily::class)
            ->set('data.family_name', $payload['family_name'])
            ->call('create')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('product_families', array_merge($payload, [
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ]));
    }

    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function it_fails_to_create_a_product_family_without_family_name(): void
    {
        $this->markTestIncomplete();
        /**
         * Missing Required Fields:
         * - family_name
         */
        $payload = [
            'family_description' => '::dummydummy::',
        ];

        Livewire::test(CreateProductFamily::class)
            ->assertStatus(422)
            ->set('data.family_name', $payload['family_name'])
            ->call('create')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('product_families', array_merge($payload, [
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ]));
    }

    /**
     * @test
     *
     * @skip Not implemented yet
     *
     * Payload for updating a product family:
     *
     *
     *            [
     *            'family_name' => 'Updated Family',
     *            ]
     */
    public function it_updates_a_product_family(): void
    {
        $this->markTestIncomplete();
        $productFamily = ProductFamily::factory()->create([
            'family_name' => '::original_product_family_name::',
        ]);

        $updatedData = [
            'family_name' => '::updated_product_family_name::',
        ];

        Livewire::test(EditProductFamily::class, ['record' => $productFamily->family_id])
            ->set('data.family_name', $updatedData['family_name'])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('product_families', array_merge($updatedData, [
            'family_id' => $productFamily->family_id,
        ]));
    }

    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function it_deletes_a_product_family(): void
    {
        $this->markTestIncomplete('needs delete action');
        $productFamily = ProductFamily::factory()->create([
            'family_name' => '::product_family_name::',
        ]);

        Livewire::test(ManageProductFamilies::class)
            ->callTableAction('delete', $productFamily)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('product_families', [
            'family_id' => $productFamily->family_id,
        ]);
    }
    // endregion
}
