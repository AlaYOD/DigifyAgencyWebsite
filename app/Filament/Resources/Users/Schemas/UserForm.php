<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\Department;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->required()->translateLabel(),
                TextInput::make('email')->email()->required()->unique(ignoreRecord: true)->translateLabel(),
                TextInput::make('password')
                    ->password()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->translateLabel(),
                Select::make('roles')
                    ->multiple()
                    ->relationship('roles', 'name')
                    ->searchable()
                    ->translateLabel(),
                Select::make('department_id')
                    ->options(fn (): array => Department::all()->mapWithKeys(fn (Department $department): array => [
                        $department->id => $department->getTranslation('name', 'en'),
                    ])->all())
                    ->searchable()
                    ->helperText('department_id is where the user belongs.')
                    ->translateLabel(),
                Select::make('managedDepartments')
                    ->multiple()
                    ->relationship('managedDepartments', 'name')
                    ->searchable()
                    ->helperText('managedDepartments are the departments the user manages.')
                    ->translateLabel(),
                Toggle::make('is_active')->default(true)->translateLabel(),
            ]);
    }
}
