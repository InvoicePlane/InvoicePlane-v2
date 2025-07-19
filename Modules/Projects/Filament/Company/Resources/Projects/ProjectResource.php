<?php

namespace Modules\Projects\Filament\Company\Resources\Projects;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Core\Filament\Company\Resources\BaseResource;
use Modules\Projects\Filament\Company\Resources\Projects\Pages\ListProjects;
use Modules\Projects\Filament\Company\Resources\Projects\Schemas\ProjectForm;
use Modules\Projects\Filament\Company\Resources\Projects\Tables\ProjectsTable;
use Modules\Projects\Models\Project;

class ProjectResource extends BaseResource
{
    protected static ?string $model = Project::class;

    //heroicon-o-briefcase
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static ?int $navigationSort = 10;

    protected static bool $shouldRegisterNavigation = true;

    protected static bool $isScopedToTenant = true;

    public static function getModelLabel(): string
    {
        return trans('ip.project');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('ip.projects');
    }

    public static function getNavigationLabel(): string
    {
        return trans('ip.projects');
    }

    public static function form(Schema $schema): Schema
    {
        return ProjectForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProjectsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProjects::route('/'),
        ];
    }
}
