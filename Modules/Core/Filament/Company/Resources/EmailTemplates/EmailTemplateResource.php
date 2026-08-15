<?php

namespace Modules\Core\Filament\Company\Resources\EmailTemplates;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Enums\UserRole;
use Modules\Core\Filament\Admin\Resources\EmailTemplates\Schemas\EmailTemplateForm;
use Modules\Core\Filament\Admin\Resources\EmailTemplates\Tables\EmailTemplatesTable;
use Modules\Core\Filament\Company\Resources\EmailTemplates\Pages\CreateEmailTemplate;
use Modules\Core\Filament\Company\Resources\EmailTemplates\Pages\EditEmailTemplate;
use Modules\Core\Filament\Company\Resources\EmailTemplates\Pages\ListEmailTemplates;
use Modules\Core\Models\EmailTemplate;

class EmailTemplateResource extends Resource
{
    protected static ?string $model = EmailTemplate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::EnvelopeOpen;

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
            'index'  => ListEmailTemplates::route('/'),
            'create' => CreateEmailTemplate::route('/create'),
            'edit'   => EditEmailTemplate::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        $roles = array_merge(UserRole::elevated(), [UserRole::CUSTOMER_ADMIN->value]);
        return auth()->user()?->hasRole($roles) ?? false;
    }

    public static function canCreate(): bool
    {
        $roles = array_merge(UserRole::elevated(), [UserRole::CUSTOMER_ADMIN->value]);
        return auth()->user()?->hasRole($roles) ?? false;
    }

    public static function canView(Model $record): bool
    {
        $roles = array_merge(UserRole::elevated(), [UserRole::CUSTOMER_ADMIN->value]);
        return auth()->user()?->hasRole($roles) ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        $roles = array_merge(UserRole::elevated(), [UserRole::CUSTOMER_ADMIN->value]);
        return auth()->user()?->hasRole($roles) ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        $roles = array_merge(UserRole::elevated(), [UserRole::CUSTOMER_ADMIN->value]);
        return auth()->user()?->hasRole($roles) ?? false;
    }
}
