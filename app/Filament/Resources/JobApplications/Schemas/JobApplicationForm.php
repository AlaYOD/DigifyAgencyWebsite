<?php

namespace App\Filament\Resources\JobApplications\Schemas;

use App\Models\JobApplication;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class JobApplicationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Candidate details')
                ->schema([
                    Placeholder::make('candidate_name')
                        ->content(fn (JobApplication $record): string => $record->display_name),
                    TextInput::make('email')->email(),
                    TextInput::make('phone'),
                ])
                ->visible(fn (): bool => auth()->user()?->can('applications.viewPii') ?? false),
            Section::make('Application')
                ->schema([
                    Textarea::make('cover_letter')->disabled(),
                    TextInput::make('portfolio_url')->url()->disabled(),
                    TextInput::make('linkedin_url')->url()->disabled(),
                ])
                ->visible(fn (): bool => auth()->user()?->can('applications.viewPii') ?? false),
            Section::make('Review')
                ->schema([
                    TextInput::make('rating')->numeric()->minValue(1)->maxValue(5),
                ])
                ->visible(fn (): bool => auth()->user()?->can('applications.note') ?? false),
            Section::make('AI summary')
                ->description('Machine-generated placeholder; scoring is not available in this sprint.')
                ->schema([
                    Placeholder::make('ai_summary')->content('No machine-generated summary yet.'),
                ]),
        ]);
    }
}
