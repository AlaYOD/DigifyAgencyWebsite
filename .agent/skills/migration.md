# Skill: Migrations

## Conventions
- Postgres 16. Use `jsonb` for translatable fields, never `json`.
- Every FK declares `onDelete` explicitly: `cascadeOnDelete()`, `nullOnDelete()`, `restrictOnDelete()`.
- `NOT NULL` wherever null is meaningless. `CHECK` constraints where the DB can enforce a rule.
- Index every FK, and every column used in `WHERE` or `ORDER BY`.
- GIN index on every jsonb column that is queried.

## Translatable pattern
```php
$table->jsonb('slug');      // {"en":"about","ar":"من-نحن"}
$table->jsonb('title');
```
```php
DB::statement('CREATE INDEX pages_slug_gin ON pages USING GIN (slug)');
```

## Partial unique index
```php
DB::statement('CREATE UNIQUE INDEX pages_single_homepage
    ON pages (is_homepage) WHERE is_homepage = true');
```

## Check constraint
```php
DB::statement('ALTER TABLE job_postings ADD CONSTRAINT salary_order
    CHECK (salary_max IS NULL OR salary_min IS NULL OR salary_max >= salary_min)');
```

## Order
locales → departments → users(+permissions, department_user) → media → settings →
categories → tags → posts → post_tag → pages → projects → menus → menu_items →
forms → form_fields → form_submissions → job_postings → pipeline_stages →
job_applications → stage_transitions → application_notes → redirects → redirect_misses → activity_log

## Never
- Name a table `jobs` (Laravel's queue owns it)
- Use `$table->json()` on Postgres
- Rely on app code alone for referential integrity
