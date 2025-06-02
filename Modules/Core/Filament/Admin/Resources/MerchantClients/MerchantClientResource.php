<?php

namespace Modules\Core\Filament\Admin\Resources\MerchantClients;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Core\Filament\Admin\Resources\MerchantClients\Pages\ListMerchantClients;
use Modules\Core\Filament\Admin\Resources\MerchantClients\Schemas\MerchantClientForm;
use Modules\Core\Filament\Admin\Resources\MerchantClients\Tables\MerchantClientsTable;
use Modules\Payments\Models\MerchantClient;

class MerchantClientResource extends Resource
{
    protected static ?string $model = MerchantClient::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPaperAirplane;

    public static function form(Schema $schema): Schema
    {
        return MerchantClientForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MerchantClientsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMerchantClients::route('/'),
        ];
    }
}
