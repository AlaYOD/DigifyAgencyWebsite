<?php

namespace App\Filament\Resources\MediaAssets;

use App\Filament\Resources\MediaAssets\Pages\CreateMediaAsset;
use App\Filament\Resources\MediaAssets\Pages\EditMediaAsset;
use App\Filament\Resources\MediaAssets\Pages\ListMediaAssets;
use App\Models\MediaAsset;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MediaAssetResource extends Resource
{
    protected static ?string $model = MediaAsset::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Content';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static ?string $navigationLabel = 'Media library';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            FileUpload::make('upload')->required(fn (string $operation): bool => $operation === 'create')->storeFiles(false)->imageEditor()->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml', 'video/mp4', 'video/webm', 'application/pdf'])->maxSize(51200)->visibleOn('create'),
            TextInput::make('name')->required(), TextInput::make('folder')->default('general')->required(), TextInput::make('credit'),
            Tabs::make('Accessibility and caption')->tabs([
                Tabs\Tab::make('English')->schema([TextInput::make('alt_text.en')->label('Alternative text'), Textarea::make('caption.en')]),
                Tabs\Tab::make('العربية')->schema([TextInput::make('alt_text.ar')->label('النص البديل')->extraAttributes(['dir' => 'rtl']), Textarea::make('caption.ar')->extraAttributes(['dir' => 'rtl'])]),
            ])->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            ImageColumn::make('preview')->state(fn (MediaAsset $record): ?string => $record->getFirstMediaUrl('default') ?: null)->square(),
            TextColumn::make('name')->searchable()->sortable(), TextColumn::make('folder')->badge(), TextColumn::make('media.file_name')->label('File'),
            TextColumn::make('media.mime_type')->label('Type'), TextColumn::make('uploader.name')->label('Uploaded by'), TextColumn::make('created_at')->dateTime()->sortable(),
        ])->filters([SelectFilter::make('folder')->options(fn (): array => MediaAsset::query()->distinct()->pluck('folder', 'folder')->all())])->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ListMediaAssets::route('/'), 'create' => CreateMediaAsset::route('/create'), 'edit' => EditMediaAsset::route('/{record}/edit')];
    }
}
