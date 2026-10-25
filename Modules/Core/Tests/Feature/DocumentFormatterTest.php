<?php

namespace Modules\Core\Tests\Feature;

use Illuminate\Support\Carbon;
use Modules\Core\Support\DocumentFormatter;
use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('smoke')]
class DocumentFormatterTest extends AbstractTestCase
{
    #[Test]
    public function it_formats_amounts_and_quantities_without_changing_precision_or_sign(): void
    {
        $de = new DocumentFormatter('de');
        $en = new DocumentFormatter('en');
        $this->assertSame('1.234,50', $de->amount(1234.5));
        $this->assertSame('-1.234,50', $de->amount(-1234.5));
        $this->assertSame('1,234.50', $en->amount(1234.5));
        $this->assertSame('1,25000001', $de->quantity(1.25000001));
        $this->assertSame('1.25000001', $en->quantity(1.25000001));
        $this->assertSame('2', $de->quantity(2));
        $this->assertSame('0,00', $de->amount(0));
    }

    #[Test]
    public function it_formats_dates_without_shifting_calendar_dates_and_honours_explicit_formats(): void
    {
        $date = Carbon::parse('2026-10-25 00:00:00', 'Europe/Berlin');
        $this->assertSame('25.10.2026', (new DocumentFormatter('de'))->date($date));
        $this->assertSame('2026-10-25', (new DocumentFormatter('en'))->date($date));
        $this->assertSame('25/10/2026', (new DocumentFormatter('de', 'd/m/Y'))->date($date));
        $this->assertSame('', (new DocumentFormatter('de'))->date(null));
        $this->assertSame('25. Oktober 2026', (new DocumentFormatter('de', 'd. F Y'))->date($date));
        $this->assertSame('2026-10-25 00:00:00', $date->format('Y-m-d H:i:s'));
    }

    #[Test]
    public function it_uses_the_request_locale_for_missing_or_unsupported_customer_languages(): void
    {
        app()->setLocale('en');
        $this->assertSame('de', DocumentFormatter::resolveLocale('de'));
        $this->assertSame('en', DocumentFormatter::resolveLocale(null));
        $this->assertSame('en', DocumentFormatter::resolveLocale('../../unknown'));
    }
}
