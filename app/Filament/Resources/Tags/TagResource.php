<?php

namespace App\Filament\Resources\Tags;

use App\Filament\Resources\Tags\Pages\CreateTag;
use App\Filament\Resources\Tags\Pages\EditTag;
use App\Filament\Resources\Tags\Pages\ListTags;
use App\Filament\Support\AdminOptions;
use App\Models\Tag;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class TagResource extends Resource
{
    protected static ?string $model = Tag::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Content';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    public static function getEloquentQuery(): Builder
    {
        return (new Tag)->scopeVisibleTo(parent::getEloquentQuery(), auth()->user());
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('Translations')->tabs([
                Tabs\Tab::make('English')->schema([TextInput::make('name.en')->required()->live(onBlur: true)->afterStateUpdated(fn ($state, $set) => $set('slug.en', Str::slug($state ?? ''))), TextInput::make('slug.en')->required()]),
                Tabs\Tab::make('العربية')->schema([TextInput::make('name.ar')->required()->extraAttributes(['dir' => 'rtl']), TextInput::make('slug.ar')->required()->extraAttributes(['dir' => 'rtl'])]),
            ]), Select::make('department_id')->options(AdminOptions::departments())->searchable(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('name')->searchable()->sortable(), TextColumn::make('slug'), TextColumn::make('posts_count')->counts('posts')])->recordActions([EditAction::make()])->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => ListTags::route('/'), 'create' => CreateTag::route('/create'), 'edit' => EditTag::route('/{record}/edit')];
    }
}
