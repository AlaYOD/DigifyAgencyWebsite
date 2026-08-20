<?php

namespace App\Filament\Resources\Menus;

use App\Filament\Resources\Menus\Pages\CreateMenu;
use App\Filament\Resources\Menus\Pages\EditMenu;
use App\Filament\Resources\Menus\Pages\ListMenus;
use App\Models\Menu;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MenuResource extends Resource
{
    protected static ?string $model = Menu::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Content';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBars3BottomLeft;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Menu')->schema([
                TextInput::make('key')->required()->unique(ignoreRecord: true)->regex('/^[a-z0-9_-]+$/'),
                Tabs::make('Name')->tabs([
                    Tabs\Tab::make('English')->schema([TextInput::make('name.en')->required()]),
                    Tabs\Tab::make('العربية')->schema([TextInput::make('name.ar')->required()->extraAttributes(['dir' => 'rtl'])]),
                ]),
            ]),
            Repeater::make('allItems')->relationship()->label('Menu items')->schema([
                TextInput::make('label.en')->label('Label (English)')->required(),
                TextInput::make('label.ar')->label('Label (العربية)')->required()->extraAttributes(['dir' => 'rtl']),
                TextInput::make('url')->helperText('Use a relative path for internal links.'),
                Select::make('target')->options(['same' => 'Same tab', 'new' => 'New tab'])->default('same')->required(),
                TextInput::make('icon'),
            ])->orderColumn('sort_order')->collapsible()->cloneable()->itemLabel(fn (array $state): ?string => $state['label']['en'] ?? null)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('name')->searchable(), TextColumn::make('key'), TextColumn::make('all_items_count')->counts('allItems')->label('Items'), TextColumn::make('updated_at')->since()])->recordActions([EditAction::make()])->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => ListMenus::route('/'), 'create' => CreateMenu::route('/create'), 'edit' => EditMenu::route('/{record}/edit')];
    }
}
