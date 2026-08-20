<?php

namespace App\Filament\Resources\Categories;

use App\Filament\Resources\Categories\Pages\CreateCategory;
use App\Filament\Resources\Categories\Pages\EditCategory;
use App\Filament\Resources\Categories\Pages\ListCategories;
use App\Filament\Support\AdminOptions;
use App\Models\Category;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Content';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFolder;

    public static function getEloquentQuery(): Builder
    {
        return (new Category)->scopeVisibleTo(parent::getEloquentQuery(), auth()->user());
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('Translations')->tabs([
                Tabs\Tab::make('English')->schema([
                    TextInput::make('name.en')->required()->live(onBlur: true)->afterStateUpdated(fn ($state, $set) => $set('slug.en', Str::slug($state ?? ''))),
                    TextInput::make('slug.en')->required(), Textarea::make('description.en'),
                ]),
                Tabs\Tab::make('العربية')->schema([
                    TextInput::make('name.ar')->required()->extraAttributes(['dir' => 'rtl']),
                    TextInput::make('slug.ar')->required()->extraAttributes(['dir' => 'rtl']), Textarea::make('description.ar')->extraAttributes(['dir' => 'rtl']),
                ]),
            ])->columnSpanFull(),
            Select::make('department_id')->options(AdminOptions::departments())->searchable(),
            Select::make('parent_id')->options(fn (): array => Category::visibleTo(auth()->user())->get()->mapWithKeys(fn (Category $category): array => [$category->id => $category->getTranslation('name', 'en')])->all())->searchable(),
            TextInput::make('sort_order')->numeric()->default(0)->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('name')->searchable()->sortable(), TextColumn::make('parent.name'), TextColumn::make('posts_count')->counts('posts'), TextColumn::make('sort_order')->sortable()])
            ->recordActions([EditAction::make()])->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => ListCategories::route('/'), 'create' => CreateCategory::route('/create'), 'edit' => EditCategory::route('/{record}/edit')];
    }
}
