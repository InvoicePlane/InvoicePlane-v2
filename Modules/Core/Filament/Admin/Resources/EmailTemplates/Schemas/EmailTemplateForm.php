<?php

namespace Modules\Core\Filament\Admin\Resources\EmailTemplates\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;
use Modules\Core\Enums\EmailTemplateType;
use Modules\Core\Services\EmailTemplateVariableResolver;

class EmailTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Schemas\Components\Group::make()
                    ->schema([
                        Section::make(heading:null)
                            ->schema([
                                TextInput::make('title')
                                    ->label(trans('ip.title'))
                                    ->required()
                                    ->autofocus(),
                                TextInput::make('from_name')
                                    ->label(trans('ip.from_name')),
                                TextInput::make('from_email')
                                    ->label(trans('ip.from_email')),
                            ])->columns(1),
                        Section::make(heading:trans('ip.cc_and_bcc'))
                            ->collapsed()
                            ->schema([
                                TextInput::make('cc')->label(trans('ip.cc')),
                                TextInput::make('bcc')->label(trans('ip.bcc')),
                            ])->columns(1),
                    ]),
                Schemas\Components\Group::make()
                    ->schema([
                        Section::make(heading:null)
                            ->schema(components: [
                                Select::make('type')
                                    ->label(trans('ip.type'))
                                    ->required()
                                    ->options(EmailTemplateType::class)
                                    ->default(null),
                                TextInput::make('subject')
                                    ->label(trans('ip.subject')),
                                Textarea::make('body')
                                    ->label(trans('ip.body'))
                                    ->rows(10)
                                    // email_templates.body is a NOT NULL
                                    // longText column with no default —
                                    // without this, a blank body passes
                                    // client validation and blows up as an
                                    // unhandled SQL 500. Shared by both the
                                    // admin and company panel resources.
                                    ->required(),
                            ])->columns(1),
                        Section::make(heading:trans('ip.available_variables'))
                            ->collapsed()
                            ->schema([
                                Schemas\Components\Text::make(fn (): HtmlString => new HtmlString(
                                    collect(app(EmailTemplateVariableResolver::class)->variables())
                                        ->map(fn (string $description, string $tag): string => '<div><code>' . e($tag) . '</code> — ' . e($description) . '</div>')
                                        ->implode('')
                                )),
                            ])->columns(1),
                    ]),
            ]);
    }
}
