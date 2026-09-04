<?php

namespace Modules\Core\Tests\Unit;

use Illuminate\Support\Facades\Artisan;
use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\Test;

class CoreServiceProviderCommandsTest extends AbstractTestCase
{
    #[Test]
    public function it_registers_every_core_console_command(): void
    {
        /* Arrange & Act */
        $registered = array_keys(Artisan::all());

        /* Assert */
        $this->assertContains('ip:migrate-v1', $registered);
        $this->assertContains('make:filament-user', $registered);
        $this->assertContains('ip:generate-observers', $registered);
        $this->assertContains('reports:sync-system', $registered);
    }
}
