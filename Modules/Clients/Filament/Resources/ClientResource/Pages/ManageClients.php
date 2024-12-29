<?php

namespace Modules\Clients\Filament\Resources\ClientResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Modules\Clients\Filament\Resources\ClientResource;

class ManageClients extends ManageRecords
{
    protected static string $resource = ClientResource::class;
    private $record;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function store(array $data): void
    {
        $this->record = $this->getModel()::create($data);
    }

    protected function update(array $data): void
    {
        $this->record->update($data);
    }

    public function destroy(): void
    {
        $this->record->delete();
    }
}
