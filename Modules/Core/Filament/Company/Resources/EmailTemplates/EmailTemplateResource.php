<?php

namespace Modules\Core\Filament\Company\Resources\EmailTemplates;

use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Enums\Permission;
use Modules\Core\Filament\Company\Resources\BaseResource;
use Modules\Core\Filament\Company\Resources\EmailTemplates\Pages\ListEmailTemplates;
use Modules\Core\Filament\Company\Resources\EmailTemplates\Schemas\EmailTemplateForm;
use Modules\Core\Filament\Company\Resources\EmailTemplates\Tables\EmailTemplatesTable;
use Modules\Core\Models\EmailTemplate;

class EmailTemplateResource extends BaseResource
{
    protected static ?string $model = EmailTemplate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Envelope;

    protected static ?string $navigationLabel = 'Email Templates';

    protected static ?string $modelLabel = 'Email Template';

    public static function form(Schema $schema): Schema
    {
        return EmailTemplateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmailTemplatesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmailTemplates::route('/'),
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
