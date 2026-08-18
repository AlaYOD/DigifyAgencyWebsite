<?php

namespace App\Filament\Resources\Departments\Schemas;

use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DepartmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Translations')->tabs([
                    Tabs\Tab::make('English')->schema([
                        TextInput::make('name.en')->required()->translateLabel(),
                        Textarea::make('description.en')->translateLabel(),
                    ]),
                    Tabs\Tab::make('العربية')->schema([
                        TextInput::make('name.ar')->required()->translateLabel(),
                        Textarea::make('description.ar')->translateLabel(),
                    ]),
                ]),
                TextInput::make('slug.en')->required()->translateLabel(),
                TextInput::make('slug.ar')->required()->translateLabel(),
                TextInput::make('sort_order')->numeric()->required()->default(0)->translateLabel(),
                Toggle::make('is_active')->default(true)->translateLabel(),
            ]);
    }
}
