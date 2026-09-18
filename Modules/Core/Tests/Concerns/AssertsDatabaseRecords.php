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
}
