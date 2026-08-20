<?php

namespace App\Filament\Forms;

use App\Models\Category;
use App\Models\Department;
use App\Models\Form;
use App\Models\Project;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class PageBuilder
{
    public static function make(string $name = 'blocks'): Builder
    {
        return Builder::make($name)
            ->label('Page blocks')
            ->blocks([
                self::heroCinematic(),
                self::heroInterior(),
                self::caseReel(),
                self::statRow(),
                self::processTriptych(),
                self::capabilityScroll(),
                self::logoMarquee(),
                self::testimonials(),
                self::characterLoop(),
                self::postsGrid(),
                self::jobsList(),
                self::faq(),
                self::form(),
                self::ctaBand(),
                self::richText(),
                self::mediaFull(),
            ])
            ->blockNumbers(false)
            ->collapsible()
            ->cloneable()
            ->reorderableWithButtons()
            ->addActionLabel('Add block')
            ->columnSpanFull();
    }

    private static function heroCinematic(): Builder\Block
    {
        return Builder\Block::make('hero_cinematic')->label('Hero — Cinematic')->schema([
            ...self::localizedText('eyebrow', 'Eyebrow'),
            ...self::localizedText('title', 'Title', true),
            ...self::localizedTextarea('body', 'Body'),
            ...self::mediaFields(),
            ...self::localizedText('cta_label', 'CTA label'),
            TextInput::make('cta_url')->label('CTA URL'),
            Select::make('text_alignment')->options(['start' => 'Start', 'center' => 'Center'])->default('start'),
            Toggle::make('dark_overlay')->default(true),
        ]);
    }

    private static function heroInterior(): Builder\Block
    {
        return Builder\Block::make('hero_interior')->label('Hero — Interior')->schema([
            ...self::localizedText('eyebrow', 'Eyebrow'),
            ...self::localizedText('title', 'Title', true),
            ...self::localizedTextarea('body', 'Body'),
            ...self::mediaFields(),
        ]);
    }

    private static function caseReel(): Builder\Block
    {
        return Builder\Block::make('case_reel')->label('Case study reel')->schema([
            ...self::localizedText('title', 'Section title'),
            Select::make('project_ids')->multiple()->searchable()->options(fn (): array => Project::query()->pluck('client_name', 'id')->all()),
            Select::make('layout')->options(['reel' => 'Reel', 'grid' => 'Grid'])->default('reel'),
        ]);
    }

    private static function statRow(): Builder\Block
    {
        return Builder\Block::make('stat_row')->label('Statistics row')->schema([
            Builder::make('items')->label('Statistics')->blocks([
                Builder\Block::make('stat')->schema([
                    TextInput::make('value')->required(),
                    ...self::localizedText('label', 'Label', true),
                ]),
            ])->minItems(1)->maxItems(6),
        ]);
    }

    private static function processTriptych(): Builder\Block
    {
        return Builder\Block::make('process_triptych')->label('Process triptych')->schema([
            ...self::localizedText('title', 'Section title'),
            Builder::make('items')->label('Steps')->blocks([
                Builder\Block::make('step')->schema([
                    ...self::localizedText('title', 'Step title', true),
                    ...self::localizedTextarea('body', 'Step body'),
                ]),
            ])->minItems(3)->maxItems(3),
        ]);
    }

    private static function capabilityScroll(): Builder\Block
    {
        return Builder\Block::make('capability_scroll')->label('Capabilities scroll')->schema([
            ...self::localizedText('title', 'Section title'),
            Builder::make('items')->blocks([
                Builder\Block::make('capability')->schema([
                    ...self::localizedText('title', 'Capability', true),
                    ...self::localizedTextarea('body', 'Description'),
                    TextInput::make('icon'),
                ]),
            ])->minItems(1),
        ]);
    }

    private static function logoMarquee(): Builder\Block
    {
        return Builder\Block::make('logo_marquee')->label('Logo marquee')->schema([
            ...self::localizedText('title', 'Section title'),
            TagsInput::make('media_ids')->label('Media IDs')->helperText('Enter uploaded media IDs.'),
        ]);
    }

    private static function testimonials(): Builder\Block
    {
        return Builder\Block::make('testimonials')->label('Testimonials')->schema([
            ...self::localizedText('title', 'Section title'),
            Builder::make('items')->blocks([
                Builder\Block::make('testimonial')->schema([
                    ...self::localizedTextarea('quote', 'Quote', true),
                    TextInput::make('author')->required(),
                    TextInput::make('role'),
                    TextInput::make('company'),
                ]),
            ])->minItems(1),
        ]);
    }

    private static function characterLoop(): Builder\Block
    {
        return Builder\Block::make('character_loop')->label('Character loop')->schema([
            ...self::localizedText('title', 'Title'),
            ...self::localizedTextarea('body', 'Body'),
            ...self::mediaFields(),
        ]);
    }

    private static function postsGrid(): Builder\Block
    {
        return Builder\Block::make('posts_grid')->label('Posts grid')->schema([
            ...self::localizedText('title', 'Section title'),
            Select::make('category_id')->options(fn (): array => Category::query()->get()->mapWithKeys(fn (Category $category): array => [$category->id => $category->getTranslation('name', 'en')])->all())->searchable(),
            TextInput::make('limit')->numeric()->minValue(1)->maxValue(24)->default(6),
            Toggle::make('featured_only'),
        ]);
    }

    private static function jobsList(): Builder\Block
    {
        return Builder\Block::make('jobs_list')->label('Jobs list')->schema([
            ...self::localizedText('title', 'Section title'),
            Select::make('department_id')->options(fn (): array => Department::query()->get()->mapWithKeys(fn (Department $department): array => [$department->id => $department->getTranslation('name', 'en')])->all())->searchable(),
            TextInput::make('limit')->numeric()->minValue(1)->maxValue(50)->default(12),
        ]);
    }

    private static function faq(): Builder\Block
    {
        return Builder\Block::make('faq')->label('FAQ')->schema([
            ...self::localizedText('title', 'Section title'),
            Builder::make('items')->blocks([
                Builder\Block::make('question')->schema([
                    ...self::localizedText('question', 'Question', true),
                    ...self::localizedTextarea('answer', 'Answer', true),
                ]),
            ])->minItems(1),
        ]);
    }

    private static function form(): Builder\Block
    {
        return Builder\Block::make('form')->label('Dynamic form')->schema([
            ...self::localizedText('title', 'Section title'),
            Select::make('form_id')->options(fn (): array => Form::query()->where('is_active', true)->get()->mapWithKeys(fn (Form $form): array => [$form->id => $form->getTranslation('name', 'en')])->all())->required()->searchable(),
        ]);
    }

    private static function ctaBand(): Builder\Block
    {
        return Builder\Block::make('cta_band')->label('CTA band')->schema([
            ...self::localizedText('title', 'Title', true),
            ...self::localizedTextarea('body', 'Body'),
            ...self::localizedText('cta_label', 'CTA label', true),
            TextInput::make('cta_url')->required(),
            Select::make('theme')->options(['navy' => 'Navy', 'coral' => 'Coral', 'white' => 'White'])->default('navy'),
        ]);
    }

    private static function richText(): Builder\Block
    {
        return Builder\Block::make('rich_text')->label('Rich text')->schema([
            RichEditor::make('content.en')->label('Content (English)')->required(),
            RichEditor::make('content.ar')->label('المحتوى (العربية)')->extraAttributes(['dir' => 'rtl']),
        ]);
    }

    private static function mediaFull(): Builder\Block
    {
        return Builder\Block::make('media_full')->label('Full-width media')->schema([
            ...self::mediaFields(),
            ...self::localizedText('caption', 'Caption'),
            Select::make('aspect_ratio')->options(['auto' => 'Auto', '16/9' => '16:9', '4/3' => '4:3', '1/1' => 'Square'])->default('auto'),
        ]);
    }

    private static function localizedText(string $name, string $label, bool $required = false): array
    {
        return [
            TextInput::make("{$name}.en")->label("{$label} (English)")->required($required),
            TextInput::make("{$name}.ar")->label("{$label} (العربية)")->required($required)->extraAttributes(['dir' => 'rtl']),
        ];
    }

    private static function localizedTextarea(string $name, string $label, bool $required = false): array
    {
        return [
            Textarea::make("{$name}.en")->label("{$label} (English)")->required($required),
            Textarea::make("{$name}.ar")->label("{$label} (العربية)")->required($required)->extraAttributes(['dir' => 'rtl']),
        ];
    }

    private static function mediaFields(): array
    {
        return [
            Select::make('media_id')->label('Media asset')->searchable()->options(fn (): array => Media::query()->orderByDesc('id')->pluck('file_name', 'id')->all()),
            TextInput::make('media_url')->label('External media URL')->url(),
            ...self::localizedText('alt', 'Alternative text'),
        ];
    }
}
