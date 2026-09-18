<?php

namespace Modules\Core\Filament\Admin\Resources\MailQueues\Pages;

use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Modules\Core\Filament\Admin\Resources\MailQueues\MailQueueResource;

class ListMailQueues extends ListRecords
{
    protected static string $resource = MailQueueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->action(fn () => Notification::make()
                    ->title(trans('ip.not_yet_implemented'))
                    ->warning()
                    ->send())
                ->modalWidth('full'),
        ];
    }
}
