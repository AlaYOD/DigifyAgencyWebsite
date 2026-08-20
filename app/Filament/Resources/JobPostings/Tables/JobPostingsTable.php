<?php

namespace App\Filament\Resources\JobPostings\Tables;

use App\Enums\EmploymentType;
use App\Enums\JobStatus;
use App\Enums\WorkplaceType;
use App\Models\Department;
use App\Models\JobPosting;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Validation\ValidationException;

class JobPostingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference_code')->searchable()->sortable(),
                TextColumn::make('title')->searchable(),
                TextColumn::make('department.name')->sortable(),
                TextColumn::make('status')->badge(),
                TextColumn::make('applications_count')->sortable(),
                TextColumn::make('published_at')->dateTime()->sortable(),
                TextColumn::make('closes_at')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('department_id')->options(fn (): array => Department::all()->mapWithKeys(fn (Department $department): array => [
                    $department->id => $department->getTranslation('name', 'en'),
                ])->all()),
                SelectFilter::make('status')->options(collect(JobStatus::cases())->mapWithKeys(fn ($case): array => [$case->value => str($case->value)->replace('_', ' ')->title()])->all()),
                SelectFilter::make('employment_type')->options(collect(EmploymentType::cases())->mapWithKeys(fn ($case): array => [$case->value => str($case->value)->replace('_', ' ')->title()])->all()),
                SelectFilter::make('workplace_type')->options(collect(WorkplaceType::cases())->mapWithKeys(fn ($case): array => [$case->value => str($case->value)->replace('_', ' ')->title()])->all()),
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('preview')
                    ->url(fn (JobPosting $record): string => url('/careers/'.$record->getTranslation('slug', app()->getLocale())))
                    ->openUrlInNewTab()
                    ->tooltip('This route will 404 until P-5.1.'),
                Action::make('close')
                    ->requiresConfirmation()
                    ->visible(fn (JobPosting $record): bool => auth()->user()?->can('close', $record) ?? false)
                    ->action(fn (JobPosting $record): bool => $record->update(['status' => 'closed'])),
                Action::make('publish')
                    ->requiresConfirmation()
                    ->visible(fn (JobPosting $record): bool => auth()->user()?->can('publish', $record) ?? false)
                    ->action(function (JobPosting $record): bool {
                        if (blank($record->getTranslation('title', 'ar'))) {
                            throw ValidationException::withMessages([
                                'title.ar' => 'Arabic title is required before publishing.',
                            ]);
                        }

                        if (blank($record->getTranslation('description', 'ar'))) {
                            throw ValidationException::withMessages([
                                'description.ar' => 'Arabic description is required before publishing.',
                            ]);
                        }

                        return $record->update([
                            'status' => JobStatus::PUBLISHED,
                            'published_at' => now(),
                        ]);
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
