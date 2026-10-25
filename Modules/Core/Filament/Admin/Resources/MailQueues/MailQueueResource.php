<?php

namespace Modules\Core\Filament\Admin\Resources\MailQueues;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Core\Filament\Admin\Resources\MailQueues\Pages\ListMailQueues;
use Modules\Core\Filament\Admin\Resources\MailQueues\Schemas\MailQueueForm;
use Modules\Core\Filament\Admin\Resources\MailQueues\Tables\MailQueuesTable;
use Modules\Core\Models\MailQueue;

class MailQueueResource extends Resource
{
    protected static ?string $model = MailQueue::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPaperAirplane;

    public static function getModelLabel(): string
    {
        return trans('fields.queued_email');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('fields.queued_emails');
    }

    public static function getNavigationLabel(): string
    {
        return trans('fields.mail_queue');
    }

    public static function form(Schema $schema): Schema
    {
        return MailQueueForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MailQueuesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMailQueues::route('/'),
        ];
    }
}
