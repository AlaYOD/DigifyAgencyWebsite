<?php

namespace App\Filament\Resources\RedirectMisses;

use App\Filament\Resources\RedirectMisses\Pages\ListRedirectMisses;
use App\Models\RedirectMiss;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RedirectMissResource extends Resource
{
    protected static ?string $model = RedirectMiss::class;

    protected static string|\UnitEnum|null $navigationGroup = 'System';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedExclamationTriangle;

    protected static ?string $navigationLabel = '404 report';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('path')->searchable()->copyable(), TextColumn::make('hits')->numeric()->sortable(), TextColumn::make('last_seen_at')->dateTime()->sortable(),
            TextColumn::make('referrer')->limit(50)->toggleable(), TextColumn::make('user_agent')->limit(50)->toggleable(isToggledHiddenByDefault: true),
        ])->defaultSort('hits', 'desc');
    }

    public static function getPages(): array
    {
        return ['index' => ListRedirectMisses::route('/')];
    }
}
