<?php

namespace Modules\Projects\Filament\Company\Resources\Tasks;

use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Core\Filament\Company\Resources\BaseResource;
use Modules\Projects\Filament\Company\Resources\Tasks\Pages\ListTasks;
use Modules\Projects\Filament\Company\Resources\Tasks\Schemas\TaskForm;
use Modules\Projects\Filament\Company\Resources\Tasks\Tables\TasksTable;
use Modules\Projects\Models\Task;

class TaskResource extends BaseResource
{
    protected static ?string $model = Task::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?int $navigationSort = 20;

    protected static bool $shouldRegisterNavigation = true;

    protected static bool $isScopedToTenant = true;

    public static function getModelLabel(): string
    {
        return trans('ip.task');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('ip.tasks');
    }

    public static function getNavigationLabel(): string
    {
        return trans('ip.tasks');
    }

    public static function form(Schema $schema): Schema
    {
        return TaskForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TasksTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTasks::route('/'),
        ];
    }
}
