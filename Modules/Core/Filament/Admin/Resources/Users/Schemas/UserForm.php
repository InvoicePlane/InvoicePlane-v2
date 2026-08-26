<?php

namespace Modules\Core\Filament\Admin\Resources\Users\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(4)
                    ->columnSpanFull()
                    ->schema([
                        Section::make(trans('ip.personal_information'))
                            ->columnSpan(1)  // 1/4 width
                            ->columns(1)
                            ->schema([
                                TextInput::make('name')
                                    ->label(trans('ip.name'))
                                    ->required()
                                    ->autofocus()
                                    ->maxLength(255),
                            ]),

                        Section::make(trans('ip.contact_information'))
                            ->columnSpan(3)  // 3/4 width
                            ->columns(2)
                            ->schema([
                                TextInput::make('email')
                                    ->label(trans('ip.email'))
                                    ->email()
                                    ->required()
                                    ->maxLength(255)
                                    // users.email has a unique DB constraint;
                                    // without this, a duplicate email passes
                                    // client validation and blows up as an
                                    // unhandled SQL 500 instead of a form
                                    // validation message (UserService::
                                    // createUser/updateUser do no uniqueness
                                    // check of their own).
                                    ->unique(ignoreRecord: true),
                                DatePicker::make('email_verified_at')
                                    ->label(trans('ip.email_verified_at')),
                                TextInput::make('password')
                                    ->label(trans('ip.password'))
                                    ->password()
                                    ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)
                                    ->required(fn ($context) => $context === 'create'),
                            ]),
                    ]),
            ]);
    }
}
