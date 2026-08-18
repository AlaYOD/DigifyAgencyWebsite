# Skill: Filament resources

## Access + scoping — BOTH required
```php
public static function canAccess(): bool
{
    return auth()->user()->can('applications.view');
}

public static function getEloquentQuery(): Builder
{
    return parent::getEloquentQuery()->visibleTo(auth()->user());
}
```
Without `getEloquentQuery()`, filters, exports, and record counts leak other
departments even when columns are hidden.

## Bilingual fields
```php
Tabs::make()->tabs([
    Tabs\Tab::make('English')->schema([
        TextInput::make('title.en')->required(),
        RichEditor::make('description.en'),
    ]),
    Tabs\Tab::make('العربية')->schema([
        TextInput::make('title.ar')->required(),
        RichEditor::make('description.ar'),
    ]),
])
```

## Conditional visibility
```php
TextColumn::make('email')
    ->visible(fn () => auth()->user()->can('applications.viewPii')),

Action::make('downloadCv')
    ->visible(fn ($record) => auth()->user()->can('viewPii', $record)),
```

## Block builder (pages)
```php
Builder::make('blocks')->blocks([
    Builder\Block::make('hero_cinematic')->schema([...]),
    Builder\Block::make('case_reel')->schema([
        Select::make('project_ids')->multiple()->relationship('projects', 'title'),
    ]),
])->collapsible()->blockNumbers(false)
```
Never store a section number in block data — it is computed from position.

## Rules
- One resource per model. Custom pages in `app/Filament/Pages/`.
- Every destructive action requires confirmation.
- Use `->translateLabel()` so admin labels follow the panel locale.
