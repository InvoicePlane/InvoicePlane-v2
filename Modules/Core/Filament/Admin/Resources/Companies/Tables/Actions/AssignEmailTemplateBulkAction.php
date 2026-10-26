<?php

namespace Modules\Core\Filament\Admin\Resources\Companies\Tables\Actions;

use Filament\Actions\BulkAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
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
                ->options(fn () => static::getEmailTemplateOptions())
                ->searchable()
                ->required(),
        ]);

        $this->action(function (Collection $records, array $data): void {
            $attachedCount = 0;

            /** @var Company $company */
            foreach ($records as $company) {
                $result = $company->emailTemplates()->syncWithoutDetaching([$data['email_template_id']]);
                $attachedCount += count($result['attached']);
            }

            Notification::make()
                ->title(trans_choice('ip.email_template_assigned', $attachedCount, ['count' => $attachedCount]))
                ->success()
                ->send();
        });

        $this->deselectRecordsAfterCompletion();
    }

    public static function getDefaultName(): ?string
    {
        return 'assignEmailTemplate';
    }

    /**
     * @return array<int, string>
     */
    public static function getEmailTemplateOptions(): array
    {
        return EmailTemplate::withoutGlobalScopes()
            ->with('company:id,name')
            ->get()
            ->mapWithKeys(fn (EmailTemplate $template) => [
                $template->id => sprintf(
                    '%s (%s)',
                    Str::headline($template->title ?? ''),
                    $template->company?->name ?? trans('ip.company')
                ),
            ])
            ->all();
    }
}
