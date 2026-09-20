<?php

namespace Modules\Core\Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Models\Setting;
use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(Setting::class)]
class SettingGetBoolTest extends AbstractTestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_defaults_to_true_when_the_key_has_never_been_set(): void
    {
        $this->assertTrue(Setting::getBool('generate_quote_number_for_draft'));
    }

    #[Test]
    public function it_honors_a_custom_default_when_the_key_has_never_been_set(): void
    {
        $this->assertFalse(Setting::getBool('some_unrelated_key', false));
    }

    #[Test]
    public function it_reads_a_truthy_stored_value_as_true(): void
    {
        Setting::saveByKey('generate_quote_number_for_draft', '1');

        $this->assertTrue(Setting::getBool('generate_quote_number_for_draft'));
    }

    #[Test]
    public function it_reads_a_falsy_stored_value_as_false(): void
    {
        Setting::saveByKey('generate_quote_number_for_draft', '0');

        $this->assertFalse(Setting::getBool('generate_quote_number_for_draft'));
    }
}
