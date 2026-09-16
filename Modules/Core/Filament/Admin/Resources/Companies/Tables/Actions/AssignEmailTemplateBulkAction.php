<?php

namespace Modules\Core\Filament\Admin\Resources\Companies\Tables\Actions;

use Filament\Actions\BulkAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection;
use Modules\Core\Models\Company;
use Modules\Core\Models\EmailTemplate;

class AssignEmailTemplateBulkAction extends BulkAction
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label(trans('ip.assign_email_template'));

        $this->icon(Heroicon::OutlinedEnvelope);

        $this->schema([
            Select::make('email_template_id')
                ->label(trans('ip.email_template'))
                /**
                 * Email templates are scoped to their owning company by the
                 * BelongsToCompany global scope, but an admin assigning
                 * templates across companies needs to pick from all of
                 * them regardless of the current session/tenant company.
                */
                ->options(fn () => EmailTemplate::withoutGlobalScopes()->pluck('title', 'id'))
                ->searchable()
                ->required(),
        ]);

        $this->action(function (Collection $records, array $data): void {
            /** @var Company $company */
            foreach ($records as $company) {
                $company->emailTemplates()->syncWithoutDetaching([$data['email_template_id']]);
            }

            Notification::make()
                ->title(trans_choice('ip.email_template_assigned', $records->count(), ['count' => $records->count()]))
                ->success()
                ->send();
        });

        $this->deselectRecordsAfterCompletion();
    }

    public static function getDefaultName(): ?string
    {
        return 'assignEmailTemplate';
    }
}
