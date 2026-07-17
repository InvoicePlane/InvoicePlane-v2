<?php

namespace Modules\Core\Traits;

/**
 * Proxies a plain "notes" form field to a single record in the shared,
 * polymorphic `notes` table (see Modules\Core\Models\Note) via the model's
 * own `notes(): MorphMany` relation, instead of a dedicated column.
 *
 * The relation requires the model's primary key, so the write is deferred to
 * the `saved` model event rather than persisted directly from the mutator --
 * mutators run during mass assignment, before a new model has an id.
 */
trait HasNotesAttribute
{
    protected ?string $pendingNotesContent = null;

    protected bool $notesAttributeWasSet = false;

    public static function bootHasNotesAttribute(): void
    {
        static::saved(function (self $model): void {
            if ( ! $model->notesAttributeWasSet) {
                return;
            }

            $model->notesAttributeWasSet = false;

            if (blank($model->pendingNotesContent)) {
                $model->notes()->delete();

                return;
            }

            $model->notes()->updateOrCreate([], [
                'company_id' => $model->company_id,
                'title'      => 'Notes',
                'noted_at'   => now(),
                'is_private' => false,
                'content'    => $model->pendingNotesContent,
            ]);
        });
    }

    public function getNotesAttribute(): ?string
    {
        return $this->notes()->first()?->content;
    }

    public function setNotesAttribute(?string $value): void
    {
        $this->pendingNotesContent  = $value;
        $this->notesAttributeWasSet = true;
    }
}
