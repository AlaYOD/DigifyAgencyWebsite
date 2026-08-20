<?php

namespace App\Filament\Resources\Locales;

use App\Filament\Resources\Locales\Pages\CreateLocale;
use App\Filament\Resources\Locales\Pages\EditLocale;
use App\Filament\Resources\Locales\Pages\ListLocales;
use App\Models\Locale;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LocaleResource extends Resource
{
    protected static ?string $model = Locale::class;

    protected static string|\UnitEnum|null $navigationGroup = 'System';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLanguage;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('code')->required()->length(2)->alpha()->unique(ignoreRecord: true)->disabledOn('edit'),
            TextInput::make('name')->required(), TextInput::make('native_name')->required(),
            Select::make('direction')->options(['ltr' => 'Left to right', 'rtl' => 'Right to left'])->required(),
            TextInput::make('sort_order')->numeric()->default(0)->required(), Toggle::make('is_default'), Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('code')->badge(), TextColumn::make('name')->searchable(), TextColumn::make('native_name'), TextColumn::make('direction')->badge(),
            IconColumn::make('is_default')->boolean(), IconColumn::make('is_active')->boolean(), TextColumn::make('sort_order')->sortable(),
        ])->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ListLocales::route('/'), 'create' => CreateLocale::route('/create'), 'edit' => EditLocale::route('/{record}/edit')];
    }
}
