<?php

namespace App\Filament\Resources\Pages;

use App\Enums\ContentStatus;
use App\Filament\Forms\PageBuilder;
use App\Filament\RelationManagers\RevisionsRelationManager;
use App\Filament\Resources\Pages\Pages\CreatePage;
use App\Filament\Resources\Pages\Pages\EditPage;
use App\Filament\Resources\Pages\Pages\ListPages;
use App\Filament\Support\AdminOptions;
use App\Models\Page;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
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

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Content';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    public static function getEloquentQuery(): Builder
    {
        return (new Page)->scopeVisibleTo(parent::getEloquentQuery(), auth()->user());
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('Content')->tabs([
                Tabs\Tab::make('English')->schema([
                    TextInput::make('title.en')->required()->live(onBlur: true)->afterStateUpdated(fn ($state, $set) => $set('slug.en', Str::slug($state ?? ''))),
                    TextInput::make('slug.en')->required(),
                    Textarea::make('excerpt.en'),
                ]),
                Tabs\Tab::make('العربية')->schema([
                    TextInput::make('title.ar')->required()->extraAttributes(['dir' => 'rtl']),
                    TextInput::make('slug.ar')->required()->extraAttributes(['dir' => 'rtl']),
                    Textarea::make('excerpt.ar')->extraAttributes(['dir' => 'rtl']),
                ]),
            ])->columnSpanFull(),
            PageBuilder::make(),
            Section::make('Publishing')->schema([
                Select::make('department_id')->options(AdminOptions::departments())->searchable(),
                Select::make('parent_id')->options(fn (): array => Page::query()->visibleTo(auth()->user())->get()->mapWithKeys(fn (Page $page): array => [
                    $page->id => $page->getTranslation('title', 'en'),
                ])->all())->searchable(),
                Select::make('template')->options(['default' => 'Default', 'landing' => 'Landing', 'minimal' => 'Minimal'])->default('default')->required(),
                Select::make('status')->options(AdminOptions::enum(ContentStatus::class))->default(ContentStatus::DRAFT->value)->required(),
                TextInput::make('published_at')->type('datetime-local'),
                TextInput::make('sort_order')->numeric()->default(0),
                Toggle::make('is_homepage'),
            ])->columns(2),
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
            TextColumn::make('title')->searchable()->sortable(),
            TextColumn::make('slug'),
            TextColumn::make('status')->badge(),
            TextColumn::make('department.name')->placeholder('Global'),
            IconColumn::make('is_homepage')->boolean(),
            TextColumn::make('updated_at')->since()->sortable(),
        ])->filters([
            SelectFilter::make('status')->options(AdminOptions::enum(ContentStatus::class)),
            SelectFilter::make('department_id')->options(AdminOptions::departments()),
        ])->recordActions([EditAction::make()])->toolbarActions([
            BulkActionGroup::make([DeleteBulkAction::make()]),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListPages::route('/'), 'create' => CreatePage::route('/create'), 'edit' => EditPage::route('/{record}/edit')];
    }

    public static function getRelations(): array
    {
        return [RevisionsRelationManager::class];
    }
}
