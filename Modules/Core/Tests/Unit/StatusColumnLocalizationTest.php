<?php

namespace Modules\Core\Tests\Unit;

use Filament\Tables\Table;
use Modules\Clients\Filament\Company\Resources\Relations\RelationManagers\ProjectsRelationManager;
use Modules\Clients\Filament\Company\Resources\Relations\RelationManagers\TasksRelationManager;
use Modules\Core\Tests\AbstractTestCase;
use Modules\Invoices\Filament\Company\Resources\Invoices\Pages\ListInvoices;
use Modules\Payments\Enums\PaymentStatus;
use Modules\Payments\Filament\Company\Resources\Payments\Tables\PaymentsTable;
use Modules\Projects\Enums\ProjectStatus;
use Modules\Projects\Enums\TaskStatus;
use PHPUnit\Framework\Attributes\Test;

class StatusColumnLocalizationTest extends AbstractTestCase
{
    #[Test]
    public function status_columns_translate_enum_states_and_handle_string_or_missing_values(): void
    {
        app('translator')->addLines(['enums.payment_status.completed' => 'Bezahlt Test', 'enums.project_status.active' => 'Aktiv Test', 'ip.task_status_completed' => 'Erledigt Test'], 'de');
        app()->setLocale('de');
        $projects = new ProjectsRelationManager();
        $tasks    = new TasksRelationManager();
        foreach ([
            [PaymentsTable::configure(Table::make(new ListInvoices())), 'payment_status', PaymentStatus::COMPLETED, 'Bezahlt Test'],
            [$projects->table(Table::make($projects)), 'project_status', ProjectStatus::ACTIVE, 'Aktiv Test'],
            [$tasks->table(Table::make($tasks)), 'task_status', TaskStatus::COMPLETED, 'Erledigt Test'],
        ] as [$table, $name, $state, $expected]) {
            $column = $table->getColumn($name);
            $this->assertSame($expected, $column->formatState($state));
            $this->assertSame($expected, $column->formatState($state->value));
            $this->assertSame('-', $column->formatState(null));
            $this->assertSame('-', $column->formatState('unknown'));
        }
    }
}
