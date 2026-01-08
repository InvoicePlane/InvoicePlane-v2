<?php

namespace Modules\Core\Tests\Concerns;

trait AssertsDatabaseRecords
{
    public function assertSeeDatabaseRecords(string $table, array $records): void
    {
        foreach ($records as $record) {
            $this->assertDatabaseHas($table, $record);
        }
    }

    /*public function assertLivewireComponentSeesRecords(TestableLivewire $component, array $values): void
    {
        foreach ($values as $value) {
            $component->assertSee($value);
        }
    }

    public function assertLivewireComponentDoesNotSeeRecords(TestableLivewire $component, array $values): void
    {
        foreach ($values as $value) {
            $component->assertDontSee($value);
        }
    }*/
}
