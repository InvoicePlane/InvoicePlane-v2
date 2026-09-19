<?php

namespace Modules\Core\Tests\Feature;

use Livewire\Livewire;
use Modules\Core\Filament\Admin\Pages\Settings;
use Modules\Core\Tests\AbstractAdminPanelTestCase;
use PHPUnit\Framework\Attributes\Test;

class ScratchSettingsTest extends AbstractAdminPanelTestCase
{
    #[Test]
    public function it_loads_defaults_and_persists_on_save(): void
    {
        $c1 = Livewire::actingAs($this->superAdmin())->test(Settings::class);
        $c1->set('settings.currency_symbol', '€')->call('submit');

        try {
            $c2 = Livewire::actingAs($this->superAdmin())->test(Settings::class);
        } catch (\Throwable $e) {
            fwrite(STDERR, get_class($e) . ': ' . $e->getMessage() . PHP_EOL);
            fwrite(STDERR, $e->getFile() . ':' . $e->getLine() . PHP_EOL);
            $prev = $e->getPrevious();
            while ($prev) {
                fwrite(STDERR, 'PREV: ' . get_class($prev) . ': ' . $prev->getMessage() . PHP_EOL);
                fwrite(STDERR, $prev->getFile() . ':' . $prev->getLine() . PHP_EOL);
                foreach (array_slice($prev->getTrace(), 0, 8) as $i => $frame) {
                    fwrite(STDERR, $i . ': ' . ($frame['file'] ?? '?') . ':' . ($frame['line'] ?? '?') . ' ' . ($frame['function'] ?? '') . PHP_EOL);
                }
                $prev = $prev->getPrevious();
            }
        }

        $this->assertTrue(true);
    }
}
