<?php

namespace App\Filament\Resources\FormSubmissions;

use App\Filament\Resources\FormSubmissions\Pages\ListFormSubmissions;
use App\Filament\Resources\FormSubmissions\Pages\ViewFormSubmission;
use App\Models\Form;
use App\Models\FormSubmission;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Placeholder;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FormSubmissionResource extends Resource
{
    protected static ?string $model = FormSubmission::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Forms';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInboxArrowDown;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return (new FormSubmission)->scopeVisibleTo(parent::getEloquentQuery(), auth()->user())->with('form');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Submission')->schema([
                Placeholder::make('form_name')->content(fn (?FormSubmission $record): string => $record?->form?->getTranslation('name', 'en') ?? ''),
                Placeholder::make('submitted_at')->content(fn (?FormSubmission $record): string => $record?->created_at?->toDayDateTimeString() ?? ''),
                KeyValue::make('data')->disabled()->columnSpanFull(),
                KeyValue::make('meta')->disabled()->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('id')->label('#')->sortable(),
            TextColumn::make('form.name')->searchable(),
            TextColumn::make('data')->label('Summary')->formatStateUsing(fn (array $state): string => collect($state)->filter(fn ($value) => is_scalar($value))->take(3)->map(fn ($value, $key) => "{$key}: {$value}")->join(' · '))->wrap(),
            TextColumn::make('spam_score')->badge()->color(fn (int $state): string => $state > 0 ? 'danger' : 'success'),
            IconColumn::make('read_at')->label('Read')->boolean(fn ($state): bool => filled($state)),
            TextColumn::make('created_at')->dateTime()->sortable(),
        ])->filters([
            SelectFilter::make('form_id')->options(fn (): array => Form::visibleTo(auth()->user())->get()->mapWithKeys(fn (Form $form): array => [$form->id => $form->getTranslation('name', 'en')])->all()),
            TernaryFilter::make('read')->queries(true: fn (Builder $query) => $query->whereNotNull('read_at'), false: fn (Builder $query) => $query->whereNull('read_at')),
        ])->recordActions([
            Action::make('view')->url(fn (FormSubmission $record): string => static::getUrl('view', ['record' => $record]))->icon('heroicon-o-eye'),
        ])->toolbarActions([
            BulkActionGroup::make([
                BulkAction::make('export')->visible(fn (): bool => auth()->user()?->can('submissions.export') ?? false)->action(function ($records): StreamedResponse {
                    return response()->streamDownload(function () use ($records): void {
                        $handle = fopen('php://output', 'w');
                        fputcsv($handle, ['id', 'form', 'data', 'submitted_at']);
                        foreach ($records as $record) {
                            fputcsv($handle, [$record->id, $record->form?->key, json_encode($record->data), $record->created_at]);
                        }
                        fclose($handle);
                    }, 'form-submissions.csv');
                }),
            ]),
        ])->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return ['index' => ListFormSubmissions::route('/'), 'view' => ViewFormSubmission::route('/{record}')];
    }
}
