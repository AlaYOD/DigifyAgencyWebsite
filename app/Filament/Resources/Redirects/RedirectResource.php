<?php

namespace App\Filament\Resources\Redirects;

use App\Filament\Resources\Redirects\Pages\CreateRedirect;
use App\Filament\Resources\Redirects\Pages\EditRedirect;
use App\Filament\Resources\Redirects\Pages\ListRedirects;
use App\Models\Redirect;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
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

class RedirectResource extends Resource
{
    protected static ?string $model = Redirect::class;

    protected static string|\UnitEnum|null $navigationGroup = 'System';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowRightCircle;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('from_path')->required()->unique(ignoreRecord: true)->startsWith('/')->helperText('Path only, for example /old-page/.'),
            TextInput::make('to_url')->required()->helperText('A relative path or an absolute HTTPS URL.'),
            Select::make('status_code')->options([301 => '301 — Permanent', 302 => '302 — Temporary', 307 => '307 — Temporary (method preserved)', 308 => '308 — Permanent (method preserved)'])->default(301)->required(),
            Select::make('locale')->options(['en' => 'English', 'ar' => 'العربية'])->nullable(), Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('from_path')->searchable()->copyable(), TextColumn::make('to_url')->limit(50), TextColumn::make('status_code')->badge(),
            TextColumn::make('locale')->badge(), TextColumn::make('hits')->numeric()->sortable(), TextColumn::make('last_hit_at')->since(), IconColumn::make('is_active')->boolean(),
        ])->recordActions([EditAction::make()])->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => ListRedirects::route('/'), 'create' => CreateRedirect::route('/create'), 'edit' => EditRedirect::route('/{record}/edit')];
    }
}
