<?php

namespace Modules\Core\Filament\Company\Resources\NoteTemplates;

use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Enums\Permission;
use Modules\Core\Filament\Company\Resources\BaseResource;
use Modules\Core\Filament\Company\Resources\NoteTemplates\Pages\ListNoteTemplates;
use Modules\Core\Filament\Company\Resources\NoteTemplates\Schemas\NoteTemplateForm;
use Modules\Core\Filament\Company\Resources\NoteTemplates\Tables\NoteTemplatesTable;
use Modules\Core\Models\NoteTemplate;

class NoteTemplateResource extends BaseResource
{
    protected static ?string $model = NoteTemplate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::DocumentText;

    protected static ?string $navigationLabel = 'Note Templates';

    protected static ?string $modelLabel = 'Note Template';

    public static function form(Schema $schema): Schema
    {
        return NoteTemplateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NoteTemplatesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNoteTemplates::route('/'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can(Permission::MANAGE_COMPANY_SETTINGS->value) ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can(Permission::MANAGE_COMPANY_SETTINGS->value) ?? false;
    }

    public static function canView(Model $record): bool
    {
        return auth()->user()?->can(Permission::MANAGE_COMPANY_SETTINGS->value) ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->can(Permission::MANAGE_COMPANY_SETTINGS->value) ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->can(Permission::MANAGE_COMPANY_SETTINGS->value) ?? false;
    }
}
