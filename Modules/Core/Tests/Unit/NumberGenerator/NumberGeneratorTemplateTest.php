<?php

namespace Modules\Core\Tests\Unit\NumberGenerator;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Modules\Core\Enums\NumberingType;
use Modules\Core\Models\Company;
use Modules\Core\Models\Numbering;
use Modules\Core\Tests\AbstractTestCase;
use Modules\Projects\Support\ProjectNumberGenerator;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class NumberGeneratorTemplateTest extends AbstractTestCase
{
    use RefreshDatabase;

    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var Company $company */
        $company       = Company::factory()->create();
        $this->company = $company;
    }

    #[Test]
    #[Group('numbering')]
    #[Group('templates')]
    public function it_replaces_year_template_with_four_digit_year(): void
    {
        /* Arrange */
        Carbon::setTestNow('2025-12-29');

        $numbering = Numbering::factory()->for($this->company)->create([
            'type'     => NumberingType::PROJECT->value,
            'name'     => 'Project Numbering with Year',
            'format'   => '{{prefix}}-{{year}}-{{number}}',
            'prefix'   => 'PRJ',
            'next_id'  => 1,
            'left_pad' => 4,
        ]);

        $generator = new ProjectNumberGenerator($this->company->id);

        /* Act */
        $number = $generator->forNumberingId($numbering->id)->generate();

        /* Assert */
        $this->assertEquals('PRJ-2025-0001', $number);
    }

    #[Test]
    #[Group('numbering')]
    #[Group('templates')]
    public function it_replaces_yy_template_with_two_digit_year(): void
    {
        /* Arrange */
        Carbon::setTestNow('2025-12-29');

        $numbering = Numbering::factory()->for($this->company)->create([
            'type'     => NumberingType::PROJECT->value,
            'name'     => 'Project Numbering with YY',
            'format'   => '{{prefix}}-{{yy}}-{{number}}',
            'prefix'   => 'PRJ',
            'next_id'  => 1,
            'left_pad' => 4,
        ]);

        $generator = new ProjectNumberGenerator($this->company->id);

        /* Act */
        $number = $generator->forNumberingId($numbering->id)->generate();

        /* Assert */
        $this->assertEquals('PRJ-25-0001', $number);
    }

    #[Test]
    #[Group('numbering')]
    #[Group('templates')]
    public function it_replaces_month_template_with_two_digit_month(): void
    {
        /* Arrange */
        Carbon::setTestNow('2025-12-29');

        $numbering = Numbering::factory()->for($this->company)->create([
            'type'     => NumberingType::PROJECT->value,
            'name'     => 'Project Numbering with Month',
            'format'   => '{{prefix}}-{{month}}-{{number}}',
            'prefix'   => 'PRJ',
            'next_id'  => 1,
            'left_pad' => 4,
        ]);

        $generator = new ProjectNumberGenerator($this->company->id);

        /* Act */
        $number = $generator->forNumberingId($numbering->id)->generate();

        /* Assert */
        $this->assertEquals('PRJ-12-0001', $number);
    }

    #[Test]
    #[Group('numbering')]
    #[Group('templates')]
    public function it_replaces_day_template_with_two_digit_day(): void
    {
        /* Arrange */
        Carbon::setTestNow('2025-12-29');

        $numbering = Numbering::factory()->for($this->company)->create([
            'type'     => NumberingType::PROJECT->value,
            'name'     => 'Project Numbering with Day',
            'format'   => '{{prefix}}-{{day}}-{{number}}',
            'prefix'   => 'PRJ',
            'next_id'  => 1,
            'left_pad' => 4,
        ]);

        $generator = new ProjectNumberGenerator($this->company->id);

        /* Act */
        $number = $generator->forNumberingId($numbering->id)->generate();

        /* Assert */
        $this->assertEquals('PRJ-29-0001', $number);
    }

    #[Test]
    #[Group('numbering')]
    #[Group('templates')]
    public function it_replaces_all_date_templates_in_complex_format(): void
    {
        /* Arrange */
        Carbon::setTestNow('2025-12-29');

        $numbering = Numbering::factory()->for($this->company)->create([
            'type'     => NumberingType::PROJECT->value,
            'name'     => 'Complex Format',
            'format'   => '{{prefix}}-{{year}}-{{month}}-{{number}}',
            'prefix'   => 'PRJ',
            'next_id'  => 12,
            'left_pad' => 6,
        ]);

        $generator = new ProjectNumberGenerator($this->company->id);

        /* Act */
        $number = $generator->forNumberingId($numbering->id)->generate();

        /* Assert */
        $this->assertEquals('PRJ-2025-12-000012', $number);
    }

    #[Test]
    #[Group('numbering')]
    #[Group('templates')]
    public function it_generates_sequential_numbers_with_year_month_format(): void
    {
        /* Arrange */
        Carbon::setTestNow('2025-01-15');

        $numbering = Numbering::factory()->for($this->company)->create([
            'type'     => NumberingType::PROJECT->value,
            'name'     => 'Sequential with Date',
            'format'   => '{{prefix}}-{{year}}-{{month}}-{{number}}',
            'prefix'   => 'PRJ',
            'next_id'  => 1,
            'left_pad' => 4,
        ]);

        $generator = new ProjectNumberGenerator($this->company->id);

        /* Act */
        $number1 = $generator->forNumberingId($numbering->id)->generate();
        $number2 = $generator->forNumberingId($numbering->id)->generate();
        $number3 = $generator->forNumberingId($numbering->id)->generate();

        /* Assert */
        $this->assertEquals('PRJ-2025-01-0001', $number1);
        $this->assertEquals('PRJ-2025-01-0002', $number2);
        $this->assertEquals('PRJ-2025-01-0003', $number3);
    }

    #[Test]
    #[Group('numbering')]
    #[Group('templates')]
    public function it_handles_format_without_number_placeholder(): void
    {
        /* Arrange */
        Carbon::setTestNow('2025-12-29');

        $numbering = Numbering::factory()->for($this->company)->create([
            'type'     => NumberingType::PROJECT->value,
            'name'     => 'Format without number',
            'format'   => '{{prefix}}-{{year}}-{{month}}-{{day}}',
            'prefix'   => 'PRJ',
            'next_id'  => 1,
            'left_pad' => 0,
        ]);

        $generator = new ProjectNumberGenerator($this->company->id);

        /* Act */
        $number = $generator->forNumberingId($numbering->id)->generate();

        /* Assert */
        $this->assertEquals('PRJ-2025-12-29', $number);
    }

    #[Test]
    #[Group('numbering')]
    #[Group('templates')]
    public function it_updates_date_templates_dynamically_over_time(): void
    {
        /* Arrange */
        $numbering = Numbering::factory()->for($this->company)->create([
            'type'     => NumberingType::PROJECT->value,
            'name'     => 'Dynamic Date Templates',
            'format'   => '{{prefix}}-{{year}}-{{month}}-{{number}}',
            'prefix'   => 'PRJ',
            'next_id'  => 1,
            'left_pad' => 3,
        ]);

        $generator = new ProjectNumberGenerator($this->company->id);

        /* Act */
        Carbon::setTestNow('2025-01-15');
        $januaryNumber = $generator->forNumberingId($numbering->id)->generate();

        Carbon::setTestNow('2025-02-20');
        $februaryNumber = $generator->forNumberingId($numbering->id)->generate();

        Carbon::setTestNow('2026-03-10');
        $marchNextYearNumber = $generator->forNumberingId($numbering->id)->generate();

        /* Assert */
        $this->assertEquals('PRJ-2025-01-001', $januaryNumber);
        $this->assertEquals('PRJ-2025-02-002', $februaryNumber);
        $this->assertEquals('PRJ-2026-03-003', $marchNextYearNumber);
    }

    #[Test]
    #[Group('numbering')]
    #[Group('templates')]
    public function it_maintains_padding_with_template_variables(): void
    {
        /* Arrange */
        Carbon::setTestNow('2025-12-29');

        $numbering = Numbering::factory()->for($this->company)->create([
            'type'     => NumberingType::PROJECT->value,
            'name'     => 'Padding with Templates',
            'format'   => '{{prefix}}-{{year}}-{{number}}',
            'prefix'   => 'PRJ',
            'next_id'  => 99,
            'left_pad' => 6,
        ]);

        $generator = new ProjectNumberGenerator($this->company->id);

        /* Act */
        $number1 = $generator->forNumberingId($numbering->id)->generate();
        $number2 = $generator->forNumberingId($numbering->id)->generate();

        /* Assert */
        $this->assertEquals('PRJ-2025-000099', $number1);
        $this->assertEquals('PRJ-2025-000100', $number2);
    }
}
