<?php

namespace Modules\Core\Tests\Unit\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Modules\Core\Enums\NumberingType;
use Modules\Core\Models\Company;
use Modules\Core\Models\Numbering;
use Modules\Core\Services\NumberingService;
use Modules\Core\Tests\AbstractTestCase;
use Modules\Expenses\Support\ExpenseNumberGenerator;
use Modules\Projects\Models\Task;
use Modules\Projects\Support\TaskNumberGenerator;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class NumberingCompanyIsolationTest extends AbstractTestCase
{
    use RefreshDatabase;

    private NumberingService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(NumberingService::class);
    }

    #[Test]
    #[Group('numbering')]
    #[Group('company-isolation')]
    public function it_allows_changing_task_numbering_format_per_company(): void
    {
        /* Arrange */
        Carbon::setTestNow('2025-12-29');

        $company = Company::factory()->create(['id' => 22]);

        $numbering = Numbering::factory()->for($company)->create([
            'type'     => NumberingType::TASK->value,
            'name'     => 'Task Numbering',
            'format'   => 'TSK-{{number}}',
            'prefix'   => 'TSK',
            'next_id'  => 1,
            'left_pad' => 4,
        ]);

        $generator = new TaskNumberGenerator($company->id);

        /* Act */
        // Generate first number with original format
        $firstNumber = $generator->forNumberingId($numbering->id)->generate();

        // Change format to include month
        $this->service->updateNumbering($numbering, [
            'format' => 'TSK-{{month}}-{{number}}',
        ]);

        // Generate second number with new format
        $secondNumber = $generator->forNumberingId($numbering->id)->generate();

        /* Assert */
        $this->assertEquals('TSK-0001', $firstNumber);
        $this->assertEquals('TSK-12-0002', $secondNumber); // Number continues, doesn't reset
    }

    #[Test]
    #[Group('numbering')]
    #[Group('company-isolation')]
    public function it_isolates_numbering_changes_between_companies(): void
    {
        /* Arrange */
        Carbon::setTestNow('2025-12-29');

        $company22 = Company::factory()->create(['id' => 22]);
        $company23 = Company::factory()->create(['id' => 23]);

        // Company 22 numbering
        $numbering22 = Numbering::factory()->for($company22)->create([
            'type'     => NumberingType::TASK->value,
            'name'     => 'Task Numbering Company 22',
            'format'   => 'TSK-{{number}}',
            'prefix'   => 'TSK',
            'next_id'  => 1,
            'left_pad' => 4,
        ]);

        // Company 23 numbering (same type, different company)
        $numbering23 = Numbering::factory()->for($company23)->create([
            'type'     => NumberingType::TASK->value,
            'name'     => 'Task Numbering Company 23',
            'format'   => 'TSK-{{number}}',
            'prefix'   => 'TSK',
            'next_id'  => 1,
            'left_pad' => 4,
        ]);

        $generator22 = new TaskNumberGenerator($company22->id);
        $generator23 = new TaskNumberGenerator($company23->id);

        /* Act */
        // Company 22 changes format
        $this->service->updateNumbering($numbering22, [
            'format' => 'TSK-{{month}}-{{number}}',
        ]);

        // Generate numbers for both companies
        $number22 = $generator22->forNumberingId($numbering22->id)->generate();
        $number23 = $generator23->forNumberingId($numbering23->id)->generate();

        /* Assert */
        $this->assertEquals('TSK-12-0001', $number22); // Company 22 uses new format with month
        $this->assertEquals('TSK-0001', $number23); // Company 23 keeps original format
    }

    #[Test]
    #[Group('numbering')]
    #[Group('company-isolation')]
    public function it_allows_changing_expense_numbering_with_year_month(): void
    {
        /* Arrange */
        Carbon::setTestNow('2025-12-29');

        $company = Company::factory()->create(['id' => 34]);

        $numbering = Numbering::factory()->for($company)->create([
            'type'     => NumberingType::EXPENSE->value,
            'name'     => 'Expense Numbering',
            'format'   => 'EXP-{{number}}',
            'prefix'   => 'EXP',
            'next_id'  => 1,
            'left_pad' => 4,
        ]);

        $generator = new ExpenseNumberGenerator($company->id);

        /* Act */
        // Generate two numbers with original format
        $firstNumber  = $generator->forNumberingId($numbering->id)->generate();
        $secondNumber = $generator->forNumberingId($numbering->id)->generate();

        // Change format to include year and month
        $this->service->updateNumbering($numbering, [
            'format' => 'EXP-{{year}}-{{month}}-{{number}}',
        ]);

        // Generate third number with new format
        $thirdNumber = $generator->forNumberingId($numbering->id)->generate();

        /* Assert */
        $this->assertEquals('EXP-0001', $firstNumber);
        $this->assertEquals('EXP-0002', $secondNumber);
        $this->assertEquals('EXP-2025-12-0003', $thirdNumber); // Number continues
    }

    #[Test]
    #[Group('numbering')]
    #[Group('company-isolation')]
    public function it_continues_numbering_after_format_change_without_reset(): void
    {
        /* Arrange */
        Carbon::setTestNow('2025-12-29');

        $company = Company::factory()->create();

        $numbering = Numbering::factory()->for($company)->create([
            'type'     => NumberingType::TASK->value,
            'name'     => 'Test Numbering',
            'format'   => 'TSK-{{number}}',
            'prefix'   => 'TSK',
            'next_id'  => 1,
            'left_pad' => 4,
        ]);

        $generator = new TaskNumberGenerator($company->id);

        /* Act */
        // Generate 5 numbers with original format
        for ($i = 1; $i <= 5; $i++) {
            $generator->forNumberingId($numbering->id)->generate();
        }

        // Change to complex format
        $this->service->updateNumbering($numbering, [
            'format' => 'TSK-{{year}}-{{month}}-{{number}}',
        ]);

        // Generate 5 more numbers with new format
        $numbers = [];
        for ($i = 6; $i <= 10; $i++) {
            $numbers[] = $generator->forNumberingId($numbering->id)->generate();
        }

        /* Assert */
        $this->assertEquals('TSK-2025-12-0006', $numbers[0]);
        $this->assertEquals('TSK-2025-12-0007', $numbers[1]);
        $this->assertEquals('TSK-2025-12-0008', $numbers[2]);
        $this->assertEquals('TSK-2025-12-0009', $numbers[3]);
        $this->assertEquals('TSK-2025-12-0010', $numbers[4]);
    }

    #[Test]
    #[Group('numbering')]
    #[Group('troubleshooting')]
    public function it_recalculates_next_id_when_set_to_lower_value_for_troubleshooting(): void
    {
        /* Arrange */
        $company = Company::factory()->create(['id' => 17]);

        $numbering = Numbering::factory()->for($company)->create([
            'type'     => NumberingType::TASK->value,
            'name'     => 'Task Numbering',
            'format'   => 'TSK-{{number}}',
            'prefix'   => 'TSK',
            'next_id'  => 45534,
            'last_id'  => 45533,
            'left_pad' => 5,
        ]);

        // Create existing task records to simulate real usage
        // Note: Tasks don't have numbering_id FK, they just store the generated number
        for ($i = 1; $i <= 5; $i++) {
            Task::factory()->for($company)->create([
                'task_number' => 'TSK-' . str_pad(45528 + $i, 5, '0', STR_PAD_LEFT),
            ]);
        }

        /* Act */
        // User tries to set next_id to 1 (troubleshooting mode)
        $result = $this->service->updateNumbering($numbering, [
            'next_id' => 1,
        ]);

        /* Assert */
        // System should automatically recalculate and find highest number
        $this->assertEquals(45534, $result->next_id); // Highest (45533) + 1

        // Verify that generating a new number works correctly
        $generator = new TaskNumberGenerator($company->id);
        $newNumber = $generator->forNumberingId($numbering->id)->generate();
        $this->assertEquals('TSK-45534', $newNumber);
    }
}
