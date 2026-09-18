<?php

namespace Modules\Core\Filament\Admin\Resources\Users\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->columnSpanFull()
                    ->schema([
                        Section::make(trans('ip.personal_information'))
                            ->icon(Heroicon::OutlinedUser)
                            ->columnSpan(2)
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('name')
                                            ->label(trans('ip.name'))
                                            ->prefixIcon(Heroicon::OutlinedIdentification)
                                            ->required()
                                            ->autofocus()
                                            ->maxLength(255)
                                            ->columnSpan(2),
                                        TextInput::make('email')
                                            ->label(trans('ip.email'))
                                            ->prefixIcon(Heroicon::OutlinedEnvelope)
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
                                            ->unique(ignoreRecord: true)
                                            ->columnSpan(1),
                                        TextInput::make('password')
                                            ->label(trans('ip.password'))
                                            ->prefixIcon(Heroicon::OutlinedLockClosed)
                                            ->password()
                                            ->dehydrateStateUsing(fn ($state) => bcrypt($state))
                                            ->dehydrated(fn ($state) => filled($state))
                                            ->required(fn ($context) => $context === 'create')
                                            ->columnSpan(1),
                                    ]),
                            ]),

                        Section::make(trans('ip.status'))
                            ->icon(Heroicon::OutlinedShieldCheck)
                            ->columnSpan(1)
                            ->schema([
                                DatePicker::make('email_verified_at')
                                    ->label(trans('ip.email_verified_at'))
                                    ->prefixIcon(Heroicon::OutlinedCalendar),
                            ]),
                    ]),
            ]);
    }
}
