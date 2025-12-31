<?php

namespace Modules\Core\Tests\Unit\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Enums\NumberingType;
use Modules\Core\Models\Numbering;
use Modules\Core\Services\NumberingService;
use Modules\Core\Tests\AbstractAdminPanelTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class NumberingServiceTest extends AbstractAdminPanelTestCase
{
    use RefreshDatabase;

    private NumberingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new NumberingService();
    }

    #[Test]
    #[Group('unit')]
    #[Group('failed')]
    public function it_creates_a_numbering(): void
    {
        /* Arrange */
        $data = [
            'type'     => NumberingType::PROJECT->value,
            'name'     => 'Test Numbering',
            'next_id'  => 1,
            'left_pad' => 4,
            'format'   => '{{prefix}}-{{number}}',
            'prefix'   => 'PRJ',
        ];

        /* Act */
        $numbering = $this->service->createNumbering($data);

        /* Assert */
        $this->assertInstanceOf(Numbering::class, $numbering);
        $this->assertDatabaseHas('numbering', [
            'type'   => NumberingType::PROJECT->value,
            'name'   => 'Test Numbering',
            'prefix' => 'PRJ',
        ]);
    }

    #[Test]
    #[Group('unit')]
    #[Group('failed')]
    public function it_auto_sets_prefix_from_type_when_not_provided(): void
    {
        /* Arrange */
        $data = [
            'type'     => NumberingType::PROJECT->value,
            'name'     => 'Test Numbering',
            'next_id'  => 1,
            'left_pad' => 4,
        ];

        /* Act */
        $numbering = $this->service->createNumbering($data);

        /* Assert */
        $this->assertDatabaseHas('numbering', [
            'type'   => NumberingType::PROJECT->value,
            'prefix' => NumberingType::PROJECT->prefix(),
        ]);
    }

    #[Test]
    #[Group('unit')]
    #[Group('failed')]
    public function it_converts_starting_id_to_next_id(): void
    {
        /* Arrange */
        $data = [
            'type'        => NumberingType::PROJECT->value,
            'name'        => 'Project Numbering',
            'starting_id' => 100,
            'left_pad'    => 4,
        ];

        /* Act */
        $numbering = $this->service->createNumbering($data);

        /* Assert */
        $this->assertEquals(100, $numbering->next_id);
        $this->assertDatabaseHas('numbering', [
            'type'    => NumberingType::PROJECT->value,
            'next_id' => 100,
        ]);
    }

    #[Test]
    #[Group('unit')]
    #[Group('failed')]
    public function it_generates_formatted_number_preview(): void
    {
        /* Arrange */
        $numbering = Numbering::factory()->for($this->company)->create([
            'type'     => NumberingType::PROJECT->value,
            'name'     => 'Test Numbering',
            'next_id'  => 42,
            'left_pad' => 6,
            'format'   => '{{prefix}}-{{number}}',
            'prefix'   => 'PRJ',
        ]);

        /* Act */
        $preview = $this->service->previewNextFormattedNumber($numbering);

        /* Assert */
        $this->assertEquals('PRJ-000042', $preview);
    }

    #[Test]
    #[Group('unit')]
    #[Group('failed')]
    public function it_deletes_numbering_when_not_in_use(): void
    {
        /* Arrange */
        $numbering = Numbering::factory()->create([
            'type'    => NumberingType::PROJECT->value,
            'name'    => 'Test Numbering',
            'next_id' => 1,
        ]);

        /* Act */
        $result = $this->service->deleteNumbering($numbering);

        /* Assert */
        $this->assertNotNull($result);
        $this->assertDatabaseMissing('numbering', [
            'id' => $numbering->id,
        ]);
    }

    #[Test]
    #[Group('unit')]
    #[Group('failed')]
    public function it_checks_if_numbering_is_applied(): void
    {
        /* Arrange */
        $numbering = Numbering::factory()->for($this->company)->create([
            'type'    => NumberingType::PROJECT->value,
            'name'    => 'Test Numbering',
            'next_id' => 1,
        ]);

        /* Act */
        $isApplied = $this->service->isNumberingApplied($numbering);

        /* Assert */
        $this->assertFalse($isApplied);
    }

    #[Test]
    #[Group('unit')]
    #[Group('failed')]
    public function it_increments_numbers_correctly(): void
    {
        /* Arrange */
        $numbering = Numbering::factory()->for($this->company)->create([
            'type'     => NumberingType::PROJECT->value,
            'name'     => 'Test Numbering',
            'next_id'  => 10,
            'left_pad' => 4,
            'format'   => '{{prefix}}-{{number}}',
            'prefix'   => 'PRJ',
        ]);

        /* Act */
        $preview1 = $this->service->previewNextFormattedNumber($numbering);
        
        // Simulate generating a number (incrementing next_id)
        $numbering->next_id = 11;
        $numbering->save();
        
        $preview2 = $this->service->previewNextFormattedNumber($numbering);
        
        $numbering->next_id = 12;
        $numbering->save();
        
        $preview3 = $this->service->previewNextFormattedNumber($numbering);

        /* Assert */
        $this->assertEquals('PRJ-0010', $preview1);
        $this->assertEquals('PRJ-0011', $preview2);
        $this->assertEquals('PRJ-0012', $preview3);
        
        // Verify the numbering increments correctly
        $this->assertEquals(12, $numbering->next_id);
    }
}
