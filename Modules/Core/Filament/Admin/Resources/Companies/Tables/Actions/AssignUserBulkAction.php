<?php

namespace Modules\Core\Filament\Admin\Resources\Companies\Tables\Actions;

use Filament\Actions\BulkAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection;
use Modules\Core\Models\Company;
use Modules\Core\Models\User;

class AssignUserBulkAction extends BulkAction
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label(trans('ip.assign_user'));

        $this->icon(Heroicon::OutlinedUserPlus);

        $this->schema([
            Select::make('user_id')
                ->label(trans('ip.user'))
                ->options(fn () => static::getUserOptions())
                ->searchable()
                ->required(),
        ]);

        $this->action(function (Collection $records, array $data): void {
            $attachedCount = 0;

            /** @var Company $company */
            foreach ($records as $company) {
                $result = $company->users()->syncWithoutDetaching([$data['user_id']]);
                $attachedCount += count($result['attached']);
            }

            Notification::make()
                ->title(trans_choice('ip.user_assigned', $attachedCount, ['count' => $attachedCount]))
                ->success()
                ->send();
        });

        $this->deselectRecordsAfterCompletion();
    }

    public static function getDefaultName(): ?string
    {
        return 'assignUser';
    }

    /**
     * @return array<int, string>
     */
    public static function getUserOptions(): array
    {
        return User::query()
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn (User $user) => [
                $user->id => sprintf('%s (%s)', $user->name, $user->email),
            ])
            ->all();
    }
}
