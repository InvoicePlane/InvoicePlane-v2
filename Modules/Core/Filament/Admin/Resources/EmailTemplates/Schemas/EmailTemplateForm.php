<?php

namespace Modules\Core\Filament\Admin\Resources\EmailTemplates\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\Core\Enums\EmailTemplateType;

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
                                    ->options(EmailTemplateType::class)
                                    ->default(null),
                                TextInput::make('subject')->label(trans('ip.subject')),
                            ])->columns(1),
                    ]),
            ]);
    }
}
