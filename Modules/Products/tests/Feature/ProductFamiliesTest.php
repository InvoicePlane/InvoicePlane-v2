<?php

namespace Modules\Products\Tests\Feature;

use Livewire\Livewire;
use Modules\Core\Models\User;
use Modules\Core\tests\AbstractTestCase;
use Modules\Products\Filament\Resources\ProductFamilyResource\Pages\CreateProductFamily;
use Modules\Products\Filament\Resources\ProductFamilyResource\Pages\EditProductFamily;
use Modules\Products\Filament\Resources\ProductFamilyResource\Pages\ManageProductFamilies;
use Modules\Products\Models\ProductFamily;

class ProductFamiliesTest extends AbstractTestCase
{
    // region CRUD Tests

    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function it_shows_product_families_index(): void
    {
        $this->markTestSkipped('Not implemented yet');
        // $this->authenticated();

        $user = User::factory()->create();
        ProductFamily::factory()->create([
            'family_name'        => '::product_family_name::',
            'family_description' => '::product_family_description::',
        ]);

        Livewire::test(ManageProductFamilies::class)
            ->assertSee('::product_family_name::')
            ->assertSee('::product_family_description::');
    }

    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function it_creates_a_product_family(): void
    {
        /**
         * Payload:
         * {
         *     "family_name": "::product_family_name::"
         * }
         */
        $payload = [
            'family_name'        => '::product_family_name::',
            'family_description' => '::product_family_description::',
        ];

        Livewire::test(CreateProductFamily::class)
            ->set('data.family_name', $payload['family_name'])
            ->set('data.family_description', $payload['family_description'])
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
        /**
         * Missing Required Fields:
         * - family_name
         */
        $payload = [
            'family_description' => '::product_family_description::',
        ];

        Livewire::test(CreateProductFamily::class)
            ->assertStatus(422)
            ->set('data.family_name', $payload['family_name'])
            ->set('data.family_description', $payload['family_description'])
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
        $productFamily = ProductFamily::factory()->create([
            'family_name'        => '::original_product_family_name::',
            'family_description' => '::original_product_family_description::',
        ]);

        $updatedData = [
            'family_name'        => '::updated_product_family_name::',
            'family_description' => '::updated_product_family_description::',
        ];

        Livewire::test(EditProductFamily::class, ['record' => $productFamily->family_id])
            ->set('data.family_name', $updatedData['family_name'])
            ->set('data.family_description', $updatedData['family_description'])
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
