<?php

namespace Modules\Core\Filament\Company\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Modules\Core\Models\NoteTemplate;

class InsertNoteTemplateAction
{
    public static function make(string $field): Action
    {
        return Action::make('insert_note_template_' . $field)
            ->label(trans('ip.insert_template'))
            ->icon('heroicon-o-document-duplicate')
            ->schema([
                Select::make('note_template_id')
                    ->label(trans('ip.template'))
                    ->options(fn () => NoteTemplate::forCompany()->pluck('template_title', 'id'))
                    ->searchable()
                    ->required(),

                Toggle::make('replace_content')
                    ->label(trans('ip.replace_existing_content'))
                    ->default(false),
            ])
            ->modalHeading('Insert Note Template')
            ->modalSubmitActionLabel('Insert')
            ->action(function (array $data, Set $set, Get $get) use ($field): void {
                $template = NoteTemplate::query()->find($data['note_template_id']);

                if ( ! $template) {
                    return;
                }

                $existing = $get($field);

                $set($field, ($data['replace_content'] || blank($existing))
                    ? $template->template_body
                    : $existing . "\n\n" . $template->template_body);
            });
    }
}
