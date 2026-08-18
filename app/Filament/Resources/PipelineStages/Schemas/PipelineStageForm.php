<?php

namespace App\Filament\Resources\PipelineStages\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PipelineStageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('key')
                    ->required()
                    ->disabledOn('edit')
                    ->helperText('The key is immutable after creation because existing records depend on it.')
                    ->translateLabel(),
                Tabs::make('Translations')->tabs([
                    Tabs\Tab::make('English')->schema([
                        TextInput::make('name.en')->required()->translateLabel(),
                    ]),
                    Tabs\Tab::make('العربية')->schema([
                        TextInput::make('name.ar')->required()->translateLabel(),
                    ]),
                ]),
                ColorPicker::make('color')->required()->translateLabel(),
                TextInput::make('sort_order')->numeric()->required()->translateLabel(),
                Toggle::make('is_default')->translateLabel(),
                Toggle::make('is_terminal')->translateLabel(),
                Select::make('outcome')->options([
                    'positive' => 'Positive',
                    'negative' => 'Negative',
                ])->nullable()->translateLabel(),
            ]);
    }
}
