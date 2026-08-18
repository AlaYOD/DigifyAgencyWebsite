<?php

namespace App\Filament\Resources\JobPostings\Schemas;

use App\Enums\EmploymentType;
use App\Enums\ExperienceLevel;
use App\Enums\JobStatus;
use App\Enums\SalaryPeriod;
use App\Enums\WorkplaceType;
use App\Models\Department;
use Closure;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class JobPostingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('reference_code')->readOnly()->translateLabel(),
                Tabs::make('Translations')->tabs([
                    Tabs\Tab::make('English')->schema([
                        self::translatedInput('title.en', true),
                        self::translatedInput('slug.en'),
                        self::translatedInput('summary.en', true),
                        self::translatedEditor('description.en', true),
                        self::translatedEditor('responsibilities.en', true),
                        self::translatedEditor('requirements.en', true),
                        self::translatedEditor('benefits.en', true),
                    ]),
                    Tabs\Tab::make('العربية')->schema([
                        self::translatedInput('title.ar'),
                        self::translatedInput('slug.ar'),
                        self::translatedInput('summary.ar'),
                        self::translatedEditor('description.ar'),
                        self::translatedEditor('responsibilities.ar'),
                        self::translatedEditor('requirements.ar'),
                        self::translatedEditor('benefits.ar'),
                    ]),
                ]),
                Select::make('department_id')
                    ->options(fn (): array => Department::all()->mapWithKeys(fn (Department $department): array => [
                        $department->id => $department->getTranslation('name', 'en'),
                    ])->all())
                    ->searchable()->required()->translateLabel(),
                Select::make('employment_type')->options(self::enumOptions(EmploymentType::class))->required()->translateLabel(),
                Select::make('workplace_type')->options(self::enumOptions(WorkplaceType::class))->required()->translateLabel(),
                Select::make('experience_level')->options(self::enumOptions(ExperienceLevel::class))->required()->translateLabel(),
                TextInput::make('experience_years_min')->numeric()->minValue(0)->translateLabel(),
                TextInput::make('city')->translateLabel(),
                TextInput::make('country_code')->length(2)->translateLabel(),
                TextInput::make('positions_count')->numeric()->minValue(1)->required()->translateLabel(),
                Toggle::make('is_featured')->translateLabel(),
                Section::make('Salary')->schema([
                    TextInput::make('salary_min')->numeric()->translateLabel(),
                    TextInput::make('salary_max')->numeric()->translateLabel(),
                    TextInput::make('salary_currency')->length(3)->translateLabel(),
                    Select::make('salary_period')->options(self::enumOptions(SalaryPeriod::class))->nullable()->translateLabel(),
                    Toggle::make('salary_is_public')
                        ->helperText('When off, salary is omitted from the public page and from structured data entirely.')
                        ->translateLabel(),
                ])->columns(2),
                Section::make('Publishing')->schema([
                    Select::make('status')
                        ->options(self::enumOptions(JobStatus::class))
                        ->required()
                        ->rules([
                            function (Get $get): Closure {
                                return function (string $attribute, mixed $value, Closure $fail) use ($get): void {
                                    if ($value !== JobStatus::PUBLISHED->value) {
                                        return;
                                    }

                                    if (blank($get('title.ar'))) {
                                        $fail('Arabic title is required before publishing.');
                                    }

                                    if (blank($get('description.ar'))) {
                                        $fail('Arabic description is required before publishing.');
                                    }
                                };
                            },
                        ])
                        ->translateLabel(),
                    TextInput::make('published_at')->type('datetime-local')->translateLabel(),
                    TextInput::make('closes_at')->type('datetime-local')->translateLabel(),
                ])->columns(2),
            ]);
    }

    private static function translatedInput(string $name, bool $required = false): TextInput
    {
        $input = TextInput::make($name)->translateLabel();

        if ($required) {
            $input->required();
        }

        if (str_starts_with($name, 'title.')) {
            $locale = str_ends_with($name, '.ar') ? 'ar' : 'en';
            $slug = "slug.$locale";
            $input->live(onBlur: true)->afterStateUpdated(function (Get $get, Set $set, ?string $state) use ($slug): void {
                $status = $get('status');
                $status = $status instanceof JobStatus ? $status->value : $status;

                if ($status !== JobStatus::PUBLISHED->value) {
                    $set($slug, Str::slug($state ?? ''));
                }
            });
        }

        return $input;
    }

    private static function translatedEditor(string $name, bool $required = false): RichEditor
    {
        $editor = RichEditor::make($name)->translateLabel();

        if ($required) {
            $editor->required();
        }

        return $editor;
    }

    private static function enumOptions(string $enum): array
    {
        return collect($enum::cases())->mapWithKeys(fn ($case): array => [$case->value => str($case->value)->replace('_', ' ')->title()])->all();
    }
}
