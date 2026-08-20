<?php

namespace App\Filament\Resources\Forms;

use App\Enums\FormFieldType;
use App\Filament\Resources\Forms\Pages\CreateForm;
use App\Filament\Resources\Forms\Pages\EditForm;
use App\Filament\Resources\Forms\Pages\ListForms;
use App\Filament\Support\AdminOptions;
use App\Models\Form;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FormResource extends Resource
{
    protected static ?string $model = Form::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Forms';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    public static function getEloquentQuery(): Builder
    {
        return (new Form)->scopeVisibleTo(parent::getEloquentQuery(), auth()->user());
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Form identity')->schema([
                TextInput::make('key')->required()->unique(ignoreRecord: true)->regex('/^[a-z0-9][a-z0-9_-]*$/'),
                Select::make('department_id')->options(AdminOptions::departments())->searchable(),
                Tabs::make('Translations')->tabs([
                    Tabs\Tab::make('English')->schema([
                        TextInput::make('name.en')->required(),
                        Textarea::make('description.en'),
                        TextInput::make('submit_label.en')->required()->default('Submit'),
                        Textarea::make('success_message.en')->required()->default('Thank you. Your submission has been received.'),
                    ]),
                    Tabs\Tab::make('العربية')->schema([
                        TextInput::make('name.ar')->required()->extraAttributes(['dir' => 'rtl']),
                        Textarea::make('description.ar')->extraAttributes(['dir' => 'rtl']),
                        TextInput::make('submit_label.ar')->required()->default('إرسال')->extraAttributes(['dir' => 'rtl']),
                        Textarea::make('success_message.ar')->required()->default('شكرًا لك. تم استلام طلبك.')->extraAttributes(['dir' => 'rtl']),
                    ]),
                ])->columnSpanFull(),
            ])->columns(2),
            Repeater::make('fields')
                ->relationship()
                ->label('Form builder')
                ->schema([
                    TextInput::make('key')->required()->regex('/^[a-z][a-z0-9_]*$/'),
                    Select::make('type')->options(AdminOptions::enum(FormFieldType::class))->required()->live(),
                    TextInput::make('label.en')->label('Label (English)')->required(),
                    TextInput::make('label.ar')->label('Label (العربية)')->required()->extraAttributes(['dir' => 'rtl']),
                    TextInput::make('placeholder.en')->label('Placeholder (English)'),
                    TextInput::make('placeholder.ar')->label('Placeholder (العربية)')->extraAttributes(['dir' => 'rtl']),
                    Textarea::make('help_text.en')->label('Help text (English)'),
                    Textarea::make('help_text.ar')->label('Help text (العربية)')->extraAttributes(['dir' => 'rtl']),
                    Repeater::make('options')->schema([
                        TextInput::make('value')->required(),
                        TextInput::make('label.en')->label('English')->required(),
                        TextInput::make('label.ar')->label('العربية')->required()->extraAttributes(['dir' => 'rtl']),
                    ])->columns(3)->visible(fn (Get $get): bool => in_array($get('type'), ['select', 'multiselect', 'radio'], true)),
                    TagsInput::make('rules')
                        ->placeholder('required, email, max:255')
                        ->helperText('These Laravel-compatible rules are also sent to the browser schema.'),
                    Select::make('width')->options(['full' => 'Full', 'half' => 'Half', 'third' => 'Third', 'two_thirds' => 'Two thirds'])->default('full')->required(),
                    TextInput::make('conditional_logic.field')->label('Show when field'),
                    Select::make('conditional_logic.operator')->options(['equals' => 'Equals', 'not_equals' => 'Does not equal', 'contains' => 'Contains'])->default('equals'),
                    TextInput::make('conditional_logic.value')->label('Condition value'),
                ])
                ->orderColumn('sort_order')
                ->collapsible()
                ->cloneable()
                ->itemLabel(fn (array $state): ?string => $state['label']['en'] ?? $state['key'] ?? null)
                ->columnSpanFull(),
            Section::make('Delivery and retention')->schema([
                TagsInput::make('notify_emails')->placeholder('forms@example.com'),
                TextInput::make('webhook_url')->url(),
                TextInput::make('redirect_url')->url(),
                TextInput::make('retention_days')->numeric()->minValue(1)->default(730)->required(),
                Toggle::make('stores_submissions')->default(true),
                Toggle::make('captcha_enabled'),
                Toggle::make('is_active')->default(true),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->searchable()->sortable(),
            TextColumn::make('key')->copyable(),
            TextColumn::make('fields_count')->counts('fields')->label('Fields'),
            TextColumn::make('submissions_count')->counts('submissions')->label('Submissions'),
            TextColumn::make('department.name')->placeholder('Global'),
            IconColumn::make('is_active')->boolean(),
            TextColumn::make('updated_at')->since(),
        ])->filters([TernaryFilter::make('is_active')])->recordActions([EditAction::make()])->toolbarActions([
            BulkActionGroup::make([DeleteBulkAction::make()]),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListForms::route('/'), 'create' => CreateForm::route('/create'), 'edit' => EditForm::route('/{record}/edit')];
    }
}
