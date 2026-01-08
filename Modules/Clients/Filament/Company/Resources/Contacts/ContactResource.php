<?php

namespace Modules\Clients\Filament\Company\Resources\Contacts;

use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Clients\Filament\Company\Resources\Contacts\Pages\ListContacts;
use Modules\Clients\Filament\Company\Resources\Contacts\Schemas\ContactForm;
use Modules\Clients\Filament\Company\Resources\Contacts\Tables\ContactsTable;
use Modules\Clients\Models\Contact;
use Modules\Core\Filament\Company\Resources\BaseResource;

class ContactResource extends BaseResource
{
    protected static ?string $model = Contact::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    public static function form(Schema $schema): Schema
    {
        return ContactForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ContactsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListContacts::route('/'),
        ];
    }
}
