---
name: digify-filament
description: Conventions and guidelines for Filament 4 admin resources, custom pages, table filters, actions, bilingual schemas, and data scoping in Digify CMS.
---

# Digify Filament 4 Guidelines

## Scoping & Access Control
Always implement both `canAccess()` and `getEloquentQuery()`:
```php
public static function canAccess(): bool
{
    return auth()->user()?->can('resource.action') ?? false;
}

public static function getEloquentQuery(): Builder
{
    return parent::getEloquentQuery()->visibleTo(auth()->user());
}
```

## Bilingual Form Schemas
Use tabs for language separation:
```php
Tabs::make('Translations')->tabs([
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

## Conditional PII Columns & Actions
```php
TextColumn::make('email')
    ->visible(fn (): bool => auth()->user()?->can('applications.viewPii') ?? false);

Action::make('downloadCv')
    ->visible(fn ($record): bool => auth()->user()?->can('viewPii', $record) ?? false);
```

## Custom Admin Pages
- Custom pages reside in `app/Filament/Pages/` (e.g., `ApplicationsBoard`).
- All interactive actions (like drag-and-drop moves) must verify model permissions before mutating state.
