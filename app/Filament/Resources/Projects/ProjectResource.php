<?php

namespace App\Filament\Resources\Projects;

use App\Enums\ContentStatus;
use App\Filament\Forms\PageBuilder;
use App\Filament\RelationManagers\RevisionsRelationManager;
use App\Filament\Resources\Projects\Pages\CreateProject;
use App\Filament\Resources\Projects\Pages\EditProject;
use App\Filament\Resources\Projects\Pages\ListProjects;
use App\Filament\Support\AdminOptions;
use App\Models\Project;
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

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Content';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBriefcase;

    public static function getEloquentQuery(): Builder
    {
        return (new Project)->scopeVisibleTo(parent::getEloquentQuery(), auth()->user());
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('client_name')->required(),
            Tabs::make('Translations')->tabs([
                Tabs\Tab::make('English')->schema([
                    TextInput::make('title.en')->required()->live(onBlur: true)->afterStateUpdated(fn ($state, $set) => $set('slug.en', Str::slug($state ?? ''))),
                    TextInput::make('slug.en')->required(), Textarea::make('summary.en'),
                ]),
                Tabs\Tab::make('العربية')->schema([
                    TextInput::make('title.ar')->required()->extraAttributes(['dir' => 'rtl']),
                    TextInput::make('slug.ar')->required()->extraAttributes(['dir' => 'rtl']), Textarea::make('summary.ar')->extraAttributes(['dir' => 'rtl']),
                ]),
            ])->columnSpanFull(),
            PageBuilder::make(),
            Section::make('Project details')->schema([
                Select::make('department_id')->options(AdminOptions::departments())->searchable(),
                TextInput::make('sector'), TextInput::make('discipline'),
                TextInput::make('year')->numeric()->minValue(1900)->maxValue((int) date('Y') + 1),
                Select::make('status')->options(AdminOptions::enum(ContentStatus::class))->default(ContentStatus::DRAFT->value)->required(),
                TextInput::make('published_at')->type('datetime-local'), TextInput::make('sort_order')->numeric()->default(0), Toggle::make('is_featured'),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('title')->searchable()->sortable(), TextColumn::make('client_name')->searchable(), TextColumn::make('sector'),
            TextColumn::make('status')->badge(), IconColumn::make('is_featured')->boolean(), TextColumn::make('year')->sortable(),
        ])->filters([SelectFilter::make('status')->options(AdminOptions::enum(ContentStatus::class))])
            ->recordActions([EditAction::make()])->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => ListProjects::route('/'), 'create' => CreateProject::route('/create'), 'edit' => EditProject::route('/{record}/edit')];
    }

    public static function getRelations(): array
    {
        return [RevisionsRelationManager::class];
    }
}
