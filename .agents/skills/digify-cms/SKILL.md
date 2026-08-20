---
name: digify-cms
description: Master rules, architectural guidelines, security policies, and session protocols for Digify CMS (Laravel 12, Filament 4, Inertia, React 19, PostgreSQL 16).
---

# Digify CMS Master Architecture & Rules

## Core Technology Stack
- **Backend**: Laravel 12 (PHP 8.3+), Composer 2
- **Admin**: Filament 4 (Panel brand `#000038`, custom pages, strict scoping)
- **Frontend**: Inertia.js 3.3, React 19, TypeScript, Tailwind CSS v4
- **Database & Queue**: PostgreSQL 16 (JSONB for translatable fields with GIN indexes), Redis 7
- **SSR**: Node 24 LTS, SSR build at `bootstrap/ssr/ssr.js`, port `13714`

## Absolute Hard Rules (Never Violate)
1. **Careers Table & Model**: Table is `job_postings`, model is `JobPosting`. NEVER `jobs`/`Job` (collides with Laravel queue).
2. **Applications Table**: `job_applications`, CVs stored via Spatie MediaLibrary on `private` disk.
3. **Bilingual & Translatable**: Translatable fields use JSONB via `spatie/laravel-translatable`. Slugs are per-locale (`slug->en`, `slug->ar`).
4. **Logical CSS Properties**: Use `ps-`, `pe-`, `ms-`, `me-`, `text-start`, `text-end`, `border-s`, `border-e`. NEVER physical properties (`pl-`, `pr-`, `ml-`, `mr-`, `text-left`, `text-right`). Enforced by ESLint.
5. **No Client Storage**: No `localStorage` or `sessionStorage`.
6. **SSR Safety**: No `window`, `document`, or `IntersectionObserver` at module scope. All GSAP/DOM effects must be in `useEffect` or `useGSAP` with scoped cleanup.
7. **Strict Authorization**: Every authorized model needs a Policy AND a Pest test asserting denial for unauthorized roles.
8. **Filament Scoping**: Every Filament resource with scoped data MUST override `getEloquentQuery()` using `->visibleTo(auth()->user())`.
9. **Execution Context**: PHP, Artisan, Composer, and NPM run inside Docker container (`docker compose exec app ...`).

## Role & Permission Matrix
- `ceo`: Full view & publish rights, sees all applications including PII, activity logs, reports.
- `manager`: Department-scoped only. Can create/update jobs and manage applications in their managed departments.
- `hr`: Full jobs and applications access across all departments, form engine access.
- `it`: System configuration, redirects, logs. Can view application counts/stages (`applications.view`) but NEVER candidate PII (`applications.viewPii`).

## Quality Gates
- Code formatting: `vendor/bin/pint`
- Static analysis: `vendor/bin/phpstan` (Level 6)
- Backend tests: `vendor/bin/pest`
- TypeScript: `npx tsc --noEmit`
- ESLint: `npx eslint .`
- SSR Smoke: `node tests/ssr-smoke.mjs`
