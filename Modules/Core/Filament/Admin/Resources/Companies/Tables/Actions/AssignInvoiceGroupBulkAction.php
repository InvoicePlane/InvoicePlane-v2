<?php

namespace Modules\Core\Filament\Admin\Resources\Companies\Tables\Actions;

use Filament\Actions\BulkAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection;
use Modules\Core\Models\Company;
use Modules\Core\Models\Numbering;

class AssignInvoiceGroupBulkAction extends BulkAction
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label(trans('ip.assign_invoice_group'));

        $this->icon(Heroicon::OutlinedHashtag);

        $this->schema([
            Select::make('numbering_id')
                ->label(trans('ip.invoice_group'))
                /**
                 * Numberings are scoped to their owning company by the
                 * BelongsToCompany global scope, but an admin assigning
                 * invoice groups across companies needs to pick from all of
                 * them regardless of the current session/tenant company.
                */
                ->options(fn () => static::getNumberingOptions())
                ->searchable()
                ->required(),
        ]);

        $this->action(function (Collection $records, array $data): void {
            $attachedCount = 0;

            /** @var Company $company */
            foreach ($records as $company) {
                $result = $company->invoiceGroups()->syncWithoutDetaching([$data['numbering_id']]);
                $attachedCount += count($result['attached']);
            }

            Notification::make()
                ->title(trans_choice('ip.invoice_group_assigned', $attachedCount, ['count' => $attachedCount]))
                ->success()
                ->send();
        });

        $this->deselectRecordsAfterCompletion();
    }

    public static function getDefaultName(): ?string
    {
        return 'assignInvoiceGroup';
    }

    /**
     * @return array<int, string>
     */
    public static function getNumberingOptions(): array
    {
        return Numbering::withoutGlobalScopes()
            ->with('company:id,name')
            ->get()
            ->mapWithKeys(fn (Numbering $numbering) => [
                $numbering->id => sprintf(
                    '%s (%s)',
                    $numbering->name,
                    $numbering->company?->name ?? trans('ip.company')
                ),
            ])
            ->all();
    }
}
