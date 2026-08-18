<?php

namespace App\Filament\Resources\PipelineStages;

use App\Filament\Resources\PipelineStages\Pages\CreatePipelineStage;
use App\Filament\Resources\PipelineStages\Pages\EditPipelineStage;
use App\Filament\Resources\PipelineStages\Pages\ListPipelineStages;
use App\Filament\Resources\PipelineStages\Schemas\PipelineStageForm;
use App\Filament\Resources\PipelineStages\Tables\PipelineStagesTable;
use App\Models\PipelineStage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PipelineStageResource extends Resource
{
    protected static ?string $model = PipelineStage::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Careers';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('hr') ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return PipelineStageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PipelineStagesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPipelineStages::route('/'),
            'create' => CreatePipelineStage::route('/create'),
            'edit' => EditPipelineStage::route('/{record}/edit'),
        ];
    }
}
