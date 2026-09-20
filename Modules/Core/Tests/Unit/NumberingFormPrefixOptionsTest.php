<?php

namespace Modules\Core\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Enums\NumberingType;
use Modules\Core\Filament\Admin\Resources\Numberings\Schemas\NumberingForm;
use Modules\Core\Models\Company;
use Modules\Core\Models\Numbering;
use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;

#[CoversClass(NumberingForm::class)]
class NumberingFormPrefixOptionsTest extends AbstractTestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_includes_every_numbering_types_default_prefix(): void
    {
        /* Act */
        $options = $this->prefixOptions();

        /* Assert */
        foreach (NumberingType::cases() as $type) {
            $this->assertArrayHasKey($type->prefix(), $options);
        }
    }

    #[Test]
    public function it_includes_distinct_prefixes_already_in_use_across_any_company(): void
    {
        /* Arrange */
        $company = Company::factory()->create();
        Numbering::factory()->for($company)->create([
            'type'   => NumberingType::PROJECT->value,
            'prefix' => 'CUSTOM-XYZ',
        ]);

        /* Act */
        $options = $this->prefixOptions();

        /* Assert */
        $this->assertArrayHasKey('CUSTOM-XYZ', $options);
    }

    #[Test]
    public function it_includes_the_currently_set_prefix_even_when_not_a_default_or_already_in_use(): void
    {
        /* Act */
        $options = $this->prefixOptions('LEGACY-PREFIX');

        /* Assert */
        $this->assertArrayHasKey('LEGACY-PREFIX', $options);
    }

    #[Test]
    public function it_does_not_duplicate_a_prefix_that_is_both_a_default_and_already_in_use(): void
    {
        /* Arrange */
        $company = Company::factory()->create();
        Numbering::factory()->for($company)->ofType(NumberingType::QUOTE)->create();

        /* Act */
        $options = $this->prefixOptions();

        /* Assert */
        $this->assertSame(
            1,
            collect($options)->keys()->filter(fn (string $key): bool => $key === NumberingType::QUOTE->prefix())->count()
        );
    }

    /**
     * @return array<string, string>
     */
    private function prefixOptions(?string $currentPrefix = null): array
    {
        $ref    = new ReflectionClass(NumberingForm::class);
        $method = $ref->getMethod('prefixOptions');
        $method->setAccessible(true);

        return $method->invoke(null, $currentPrefix);
    }
}
