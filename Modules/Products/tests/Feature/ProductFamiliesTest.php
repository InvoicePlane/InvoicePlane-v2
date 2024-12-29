<?php

namespace Modules\Products\Tests\Feature;

use Modules\Core\tests\AbstractTestCase;

class ProductFamiliesTest extends AbstractTestCase
{
    // region CRUD Tests

    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function it_lists_product_families(): void
    {
        $this->markTestSkipped('Not implemented yet');
        // $this->authenticated();

        $response = $this->getJson(route('filament.ivpl.resources.filament.resources.productfamilies.index'));
        $response->assertStatus(200);
    }

    /** @test */
    public function it_can_create_a_product_family(): void
    {
        /**
         * Payload:
         * {
         *     "family_name": "example_family"
         * }
         */
        $payload = [
            'family_name' => 'example_family',
        ];

        $response = $this->post(route('filament.ivpl.resources.filament.resources.product-families.store'), $payload);

        $response->assertStatus(201);
        $this->assertDatabaseHas('product_families', $payload);
    }

    /** @test */
    public function it_fails_to_create_a_product_family_without_family_name(): void
    {
        /**
         * Missing Required Fields:
         * - family_name
         */
        $payload = [
            'family_name' => null,
        ];

        $response = $this->post(route('filament.ivpl.resources.filament.resources.product-families.store'), $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['family_name']);
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
        $payload = [
            'family_name' => 'Updated Family',
        ];

        $this->markTestSkipped('Not implemented yet');
        // $this->authenticated();

        $response = $this->putJson(route('filament.ivpl.resources.filament.resources.productfamilies.update', ['record' => 1]), $payload);
        $response->assertStatus(200);
    }

    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function it_deletes_a_product_family(): void
    {
        $this->markTestSkipped('Not implemented yet');
        // $this->authenticated();

        $response = $this->deleteJson(route('filament.ivpl.resources.filament.resources.productfamilies.delete', ['record' => 1]));
        $response->assertStatus(200);
    }

    // endregion
}
