<?php

namespace App\Filament\Resources\Activities;

use App\Filament\Resources\Activities\Pages\ListActivities;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Spatie\Activitylog\Models\Activity;

class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static string|\UnitEnum|null $navigationGroup = 'System';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static ?string $navigationLabel = 'Activity log';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('created_at')->dateTime()->sortable(), TextColumn::make('causer.name')->label('User')->placeholder('System'),
            TextColumn::make('event')->badge(), TextColumn::make('log_name')->badge(), TextColumn::make('description')->wrap(),
            TextColumn::make('subject_type')->formatStateUsing(fn (?string $state): string => $state ? class_basename($state) : '—'), TextColumn::make('subject_id')->label('Record'),
        ])->filters([SelectFilter::make('event')->options(['created' => 'Created', 'updated' => 'Updated', 'deleted' => 'Deleted'])])->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return ['index' => ListActivities::route('/')];
    }
}
