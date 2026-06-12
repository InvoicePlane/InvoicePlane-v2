<?php

namespace Modules\Core\Filament\Admin\Resources\DocumentGroups;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Core\Filament\Admin\Resources\DocumentGroups\Pages\ListDocumentGroups;
use Modules\Core\Filament\Admin\Resources\DocumentGroups\Schemas\DocumentGroupForm;
use Modules\Core\Filament\Admin\Resources\DocumentGroups\Tables\DocumentGroupsTable;
use Modules\Core\Models\DocumentGroup;

class DocumentGroupResource extends Resource
{
    protected static ?string $model = DocumentGroup::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::DocumentDuplicate;

    public static function form(Schema $schema): Schema
    {
        return DocumentGroupForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DocumentGroupsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDocumentGroups::route('/'),
        ];
    }
}
