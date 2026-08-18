# Digify CMS — Agent Instructions

Laravel 12 · Filament 4 · Inertia · React 19 · TypeScript · PostgreSQL 16 · Tailwind

## Environment

PHP 8.3+ · Node 24 LTS · PostgreSQL 16 · Composer 2

## Hard rules (never violate)

1. Careers table is `job_postings`. Model is `JobPosting`. NEVER `jobs`/`Job` — Laravel's queue owns that name.
2. Applications table is `job_applications`.
3. Translatable fields are JSONB via `spatie/laravel-translatable`. Slugs are per-locale.
4. CSS: use `ps- pe- ms- me- text-start text-end border-s`. NEVER `pl- pr- ml- mr- text-left text-right`.
5. No `localStorage` / `sessionStorage`.
6. Animation init only inside `useEffect`. SSR runs in Node — no `window` at module scope.
7. Every authorised model needs a Policy AND a test asserting denial.
8. Filament resources with scoped data MUST override `getEloquentQuery()`.

## Structure

```
app/Filament/Resources/       admin
app/Http/Controllers/Web/     returns Inertia::render only — thin
app/Http/Requests/            all validation
app/Http/Resources/           all output shaping
app/Policies/                 one per model
app/Services/                 business logic
app/Observers/                cache invalidation
resources/js/Pages/           Inertia pages
resources/js/Components/blocks/   block library + registry
```

## Skills — read ONLY when the task needs it

| Task type | Read |
|---|---|
| Writing a migration | `.agent/skills/migration.md` |
| Writing a model | `.agent/skills/model.md` |
| Policy or auth test | `.agent/skills/policy-testing.md` |
| Filament resource | `.agent/skills/filament-resource.md` |
| Inertia page/controller | `.agent/skills/inertia-page.md` |
| Block component | `.agent/skills/block-component.md` |
| Anything with animation | `.agent/skills/ssr-animation.md` |
| Anything user-facing | `.agent/skills/rtl.md` |
| Dynamic forms | `.agent/skills/form-engine.md` |
| Meta tags / structured data | `.agent/skills/seo.md` |

Do not read a skill file the current task does not need.

## Session protocol

- Start: read `.agent/state/STATE.md`. Nothing else.
- End: update `STATE.md` (what changed, what's next, gotchas). Keep under 40 lines.
- Decisions that alter architecture: append one line to `.agent/state/DECISIONS.md`.
- NEVER read `docs/SDD.md` or `docs/PLAYBOOK.md` in full. Ask for the specific section.
- `docs/` holds large planning documents. NEVER read them. If you think you need one, ask me for the specific section and I will paste it.

## Definition of done

`vendor/bin/pint` · `vendor/bin/phpstan` · `vendor/bin/pest` · `npx tsc --noEmit` · `npx eslint .` — all clean.
