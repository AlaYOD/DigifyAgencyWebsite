<?php

namespace App\Filament\Resources\JobApplications\Tables;

use App\Models\Department;
use App\Models\JobApplication;
use App\Models\PipelineStage;
use App\Models\StageTransition;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\StreamedResponse;

class JobApplicationsTable
{
    public static function configure(Table $table): Table
    {
        $canSeePii = fn (): bool => auth()->user()?->can('applications.viewPii') ?? false;

        return $table
            ->columns([
                TextColumn::make('display_name')
                    ->state(fn (JobApplication $record): string => $canSeePii()
                        ? $record->display_name
                        : "Candidate #{$record->id}"),
                TextColumn::make('jobPosting.reference_code')->label('Reference'),
                TextColumn::make('pipelineStage.name')->badge()
                    ->color(fn (JobApplication $record): string => $record->pipelineStage?->color ?? 'gray'),
                TextColumn::make('ai_score')->sortable(),
                TextColumn::make('rating'),
                TextColumn::make('applied_at')->dateTime()->sortable(),
                TextColumn::make('is_read')->boolean(),
                TextColumn::make('email')->visible($canSeePii),
                TextColumn::make('phone')->visible($canSeePii),
            ])
            ->filters([
                SelectFilter::make('job_posting_id')->relationship('jobPosting', 'reference_code'),
                SelectFilter::make('pipeline_stage_id')->relationship('pipelineStage', 'key'),
                SelectFilter::make('department_id')
                    ->options(fn (): array => Department::all()->mapWithKeys(fn (Department $department): array => [
                        $department->id => $department->getTranslation('name', 'en'),
                    ])->all())
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['value'] ?? null,
                        fn (Builder $query, $id): Builder => $query->whereHas(
                            'jobPosting',
                            fn (Builder $posting): Builder => $posting->where('department_id', $id),
                        ),
                    )),
                SelectFilter::make('rating')->options([1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5']),
                SelectFilter::make('has_score')
                    ->options(['yes' => 'Has score', 'no' => 'No score'])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when(($data['value'] ?? null) === 'yes', fn (Builder $query): Builder => $query->whereNotNull('ai_score'))
                        ->when(($data['value'] ?? null) === 'no', fn (Builder $query): Builder => $query->whereNull('ai_score'))),
                Filter::make('applied_at')->form([
                    DatePicker::make('from'),
                    DatePicker::make('until'),
                ])->query(fn (Builder $query, array $data): Builder => $query
                    ->when($data['from'] ?? null, fn (Builder $query, $date): Builder => $query->whereDate('applied_at', '>=', $date))
                    ->when($data['until'] ?? null, fn (Builder $query, $date): Builder => $query->whereDate('applied_at', '<=', $date))),
            ])
            ->recordActions([
                Action::make('downloadCv')
                    ->url(fn (JobApplication $record): string => URL::temporarySignedRoute(
                        'admin.job-applications.cv',
                        now()->addMinutes(15),
                        ['application' => $record],
                    ))
                    ->visible(fn (JobApplication $record): bool => auth()->user()?->can('viewPii', $record) ?? false)
                    ->openUrlInNewTab(),
                Action::make('changeStage')
                    ->form([
                        Select::make('pipeline_stage_id')
                            ->options(fn (): array => PipelineStage::ordered()->get()->mapWithKeys(fn (PipelineStage $stage): array => [
                                $stage->id => $stage->getTranslation('name', 'en'),
                            ])->all())
                            ->required(),
                    ])
                    ->visible(fn (JobApplication $record): bool => auth()->user()?->can('move', $record) ?? false)
                    ->action(function (JobApplication $record, array $data): void {
                        StageTransition::create([
                            'job_application_id' => $record->id,
                            'from_stage_id' => $record->pipeline_stage_id,
                            'to_stage_id' => $data['pipeline_stage_id'],
                            'user_id' => auth()->id(),
                            'created_at' => now(),
                        ]);
                        $record->update(['pipeline_stage_id' => $data['pipeline_stage_id']]);
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('export')
                        ->visible(fn (): bool => (auth()->user()?->hasAnyRole(['ceo', 'hr']) ?? false)
                            && (auth()->user()?->can('applications.export') ?? false))
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records): StreamedResponse {
                            return response()->streamDownload(function () use ($records): void {
                                $handle = fopen('php://output', 'w');
                                fputcsv($handle, ['id', 'display_name', 'reference', 'stage', 'applied_at']);
                                foreach ($records as $record) {
                                    fputcsv($handle, [
                                        $record->id,
                                        $record->display_name,
                                        $record->jobPosting?->reference_code,
                                        $record->pipelineStage?->key,
                                        $record->applied_at,
                                    ]);
                                }
                                fclose($handle);
                            }, 'applications.csv');
                        }),
                ]),
            ]);
    }
}
