<?php

namespace App\Filament\Resources\Posts;

use App\Enums\ContentStatus;
use App\Filament\RelationManagers\RevisionsRelationManager;
use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Filament\Resources\Posts\Pages\ListPosts;
use App\Filament\Support\AdminOptions;
use App\Models\Category;
use App\Models\Post;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Content';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNewspaper;

    public static function getEloquentQuery(): Builder
    {
        return (new Post)->scopeVisibleTo(parent::getEloquentQuery(), auth()->user());
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('Article')->tabs([
                Tabs\Tab::make('English')->schema([
                    TextInput::make('title.en')->required()->live(onBlur: true)->afterStateUpdated(fn ($state, $set) => $set('slug.en', Str::slug($state ?? ''))),
                    TextInput::make('slug.en')->required(),
                    Textarea::make('excerpt.en'),
                    RichEditor::make('body.en')->required(),
                ]),
                Tabs\Tab::make('العربية')->schema([
                    TextInput::make('title.ar')->required()->extraAttributes(['dir' => 'rtl']),
                    TextInput::make('slug.ar')->required()->extraAttributes(['dir' => 'rtl']),
                    Textarea::make('excerpt.ar')->extraAttributes(['dir' => 'rtl']),
                    RichEditor::make('body.ar')->required()->extraAttributes(['dir' => 'rtl']),
                ]),
            ])->columnSpanFull(),
            Section::make('Classification')->schema([
                Select::make('department_id')->options(AdminOptions::departments())->searchable(),
                Select::make('category_id')->options(fn (): array => Category::visibleTo(auth()->user())->get()->mapWithKeys(fn (Category $category): array => [$category->id => $category->getTranslation('name', 'en')])->all())->required()->searchable(),
                Select::make('tags')->relationship('tags', 'name')->multiple()->preload()->searchable(),
                Toggle::make('is_featured'),
            ])->columns(2),
            Section::make('Publishing')->schema([
                Select::make('status')->options(AdminOptions::enum(ContentStatus::class))->default(ContentStatus::DRAFT->value)->required(),
                TextInput::make('published_at')->type('datetime-local'),
                TextInput::make('reading_time')->numeric()->minValue(1)->default(1),
            ])->columns(3),
            Section::make('SEO')->schema([
                TextInput::make('seo.en.title')->label('SEO title (English)'),
                TextInput::make('seo.ar.title')->label('SEO title (العربية)')->extraAttributes(['dir' => 'rtl']),
                Textarea::make('seo.en.description')->label('SEO description (English)'),
                Textarea::make('seo.ar.description')->label('SEO description (العربية)')->extraAttributes(['dir' => 'rtl']),
            ])->columns(2)->collapsed(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('title')->searchable()->sortable(), TextColumn::make('category.name'),
            TextColumn::make('status')->badge(), IconColumn::make('is_featured')->boolean(),
            TextColumn::make('published_at')->dateTime()->sortable(), TextColumn::make('updated_at')->since(),
        ])->filters([SelectFilter::make('status')->options(AdminOptions::enum(ContentStatus::class))])
            ->recordActions([EditAction::make()])->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => ListPosts::route('/'), 'create' => CreatePost::route('/create'), 'edit' => EditPost::route('/{record}/edit')];
    }

    public static function getRelations(): array
    {
        return [RevisionsRelationManager::class];
    }
}
