<?php

namespace Modules\Subscriptions\Filament\Company\Resources\Subscriptions;

use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Core\Filament\Company\Resources\BaseResource;
use Modules\Subscriptions\Filament\Company\Resources\Subscriptions\Pages\CreateSubscription;
use Modules\Subscriptions\Filament\Company\Resources\Subscriptions\Pages\EditSubscription;
use Modules\Subscriptions\Filament\Company\Resources\Subscriptions\Pages\ListSubscriptions;
use Modules\Subscriptions\Filament\Company\Resources\Subscriptions\Schemas\SubscriptionForm;
use Modules\Subscriptions\Filament\Company\Resources\Subscriptions\Tables\SubscriptionsTable;
use Modules\Subscriptions\Models\Subscription;

class SubscriptionResource extends BaseResource
{
    protected static ?string $model = Subscription::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowPath;

    protected static ?int $navigationSort = 15;

    protected static bool $shouldRegisterNavigation = true;

    protected static bool $isScopedToTenant = true;

    public static function getModelLabel(): string
    {
        return trans('ip.subscription');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('ip.subscriptions');
    }

    public static function getNavigationLabel(): string
    {
        return trans('ip.subscriptions');
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getEloquentQuery()->count();
    }

    public static function form(Schema $schema): Schema
    {
        return SubscriptionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SubscriptionsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListSubscriptions::route('/'),
            'create' => CreateSubscription::route('/create'),
            'edit'   => EditSubscription::route('/{record}/edit'),
        ];
    }
}
