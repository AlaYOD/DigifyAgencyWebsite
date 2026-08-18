# Codex Prompt Pack — Digify CMS

Copy prompts one at a time. Each is short by design.

---

## How this saves tokens

| Technique | How it's applied here |
|---|---|
| **Progressive disclosure** | `AGENTS.md` is 56 lines and always loaded. Skills are 40–68 lines each and loaded only when a task needs them. A migration task never loads the SSR skill. |
| **Context optimisation** | The SDD (48 pages) and Playbook are **never** loaded. Each prompt carries only the spec lines that task needs. |
| **Prompt optimisation** | Prompts state task, constraint, and acceptance criteria. No re-explaining the stack — that lives in `AGENTS.md`. |
| **Context compression** | `STATE.md` (≤40 lines) is the entire session handoff. Start each session by reading it, not by re-reading code. |
| **Memory** | `DECISIONS.md` is append-only, one line per decision. It survives every context reset. |
| **Skills** | Ten reusable skill files replace re-pasting the same conventions into every prompt. |

**Rough budget:** ~800 tokens always-loaded, plus ~600 for one skill, plus ~200 for the prompt. Under 2K per task versus 40K+ if the SDD were in context.

---

## Rules for every session

1. **Start:** `Read .agent/state/STATE.md and tell me what's next. Do not read anything else yet.`
2. **One task per session.** Two tasks in one context produces worse code on both.
3. **Compact when the context feels heavy:** `Update .agent/state/STATE.md with what changed, what's next, and any gotcha. Keep it under 40 lines.` Then start a fresh session.
4. **Never say "read the SDD."** Paste the specific lines instead.
5. **If Codex proposes a pattern not in a skill file,** either reject it or add it to the skill file. Never let it drift.

---

# Phase 0 — Bootstrap

### P-0.1 · Scaffold
```
Create a new Laravel 12 project.

Install: filament/filament ^4.0, inertiajs/inertia-laravel,
spatie/laravel-translatable, spatie/laravel-medialibrary,
spatie/laravel-permission, spatie/laravel-activitylog,
spatie/laravel-settings, spatie/laravel-typescript-transformer.

Dev: pestphp/pest, larastan/larastan, laravel/pint.

NPM: @inertiajs/react, react 19, react-dom, react-hook-form,
@hookform/resolvers, zod, @gsap/react, gsap, lucide-react, clsx,
tailwind-merge. Set package.json engines to {"node": ">=24"}. Dev: typescript, @types/react, @types/node, tailwindcss,
@tailwindcss/vite, eslint, prettier, @vitejs/plugin-react.

Run filament:install --panels. Configure Postgres. Verify artisan serve works.
```

### P-0.2 · Quality gates
```
Configure Pint (Laravel preset), PHPStan level 6, TypeScript strict mode,
and ESLint.

Add this ESLint rule — it enforces RTL correctness and must fail the build:

'no-restricted-syntax': ['error', {
  selector: "Literal[value=/\\b(pl|pr|ml|mr|text-left|text-right)-?\\w*/]",
  message: 'Use logical properties (ps/pe/ms/me/text-start/text-end) — this project is RTL-first.'
}]

Add composer scripts: pint, phpstan, test.
Verify the ESLint rule fires on a deliberate `pl-4`, then remove it.
```

### P-0.3 · CI
```
Create .github/workflows/ci.yml with two jobs.

backend: Postgres 16 service, PHP 8.3, composer install, pint --test,
phpstan, pest, composer audit.

frontend: Node 24, npm ci, tsc --noEmit, eslint, npm run build,
npm run build:ssr, npm audit --audit-level=high.

Both must pass on an empty project.
```

---

# Phase 1 — Foundation

> Read `.agent/skills/migration.md` before P-1.1 to P-1.5.
> Read `.agent/skills/model.md` before P-1.6.
> Read `.agent/skills/policy-testing.md` before P-1.7 and P-1.8.

### P-1.1 · Locales, departments, users
```
Read .agent/skills/migration.md.

Create migrations:

locales: code char(2) unique, name, native_name, direction enum(rtl,ltr),
is_default bool, is_active bool, sort_order.

departments: slug jsonb, name jsonb, description jsonb nullable,
sort_order, is_active.

Extend users: department_id FK nullable nullOnDelete, is_active bool default true,
last_login_at nullable.

department_user pivot: department_id + user_id, composite PK, both cascade.
(This is which departments a user MANAGES, distinct from users.department_id
which is where they belong.)

Seed locales with en (LTR, default) and ar (RTL).
```

### P-1.2 · Permissions and roles
```
Read .agent/skills/policy-testing.md.

Publish spatie/laravel-permission migrations.

Create a RolePermissionSeeder with these permissions:
pages.{view,create,update,publish,delete}, posts.*, projects.*, menus.{view,manage},
media.upload, jobs.{view,create,update,publish,close},
applications.{view,viewPii,move,note,export,delete},
forms.{view,manage}, submissions.{view,export},
users.manage, settings.manage, redirects.manage, activity.view, reports.view

Four roles:
- ceo: view+publish+delete content, all applications INCLUDING viewPii, activity, reports
- manager: create/update/publish content, create/update jobs, applications view+viewPii+move+note, reports
- hr: view content only, full jobs, full applications, forms, submissions, reports
- it: view content, menus.manage, jobs.view, applications.view (NOT viewPii),
      forms.manage, submissions.view, users.manage, settings.manage,
      redirects.manage, activity.view

The absence of applications.viewPii from `it` is the entire privacy boundary.
Do not add it.
```

### P-1.3 · Content migrations
```
Read .agent/skills/migration.md.

Create migrations for: categories, tags, posts, post_tag, pages, projects,
menus, menu_items.

Translatable columns are jsonb: slug, title, excerpt, body/description, name.
pages has: parent_id self FK, blocks jsonb, template varchar, status enum,
published_at, seo jsonb, sort_order, is_homepage bool, softDeletes.

Add a partial unique index so only one page can be the homepage.
Add GIN indexes on every queried jsonb column.
menu_items uses nullable morphs for linkable_type/linkable_id.
```

### P-1.4 · Forms migrations
```
Read .agent/skills/migration.md.

forms: key varchar unique, name jsonb, description jsonb, submit_label jsonb,
success_message jsonb, redirect_url nullable, notify_emails jsonb,
webhook_url nullable, stores_submissions bool, requires_captcha bool,
retention_days smallint nullable, is_active bool.

form_fields: form_id cascade, key, type enum (text,textarea,email,tel,number,
date,select,multiselect,radio,checkbox,file,heading,paragraph),
label/placeholder/help_text jsonb, options jsonb nullable, rules jsonb,
width enum(full,half,third), conditional_logic jsonb nullable, sort_order.
Unique (form_id, key).

form_submissions: form_id cascade, data jsonb, meta jsonb, spam_score nullable,
read_at nullable, created_at. GIN index on data.
```

### P-1.5 · Careers migrations
```
Read .agent/skills/migration.md.

CRITICAL: the table is job_postings. NEVER `jobs` — Laravel's queue owns that name.

job_postings: department_id FK restrictOnDelete, form_id FK nullable,
reference_code varchar(20) unique, title/slug/summary/description/
responsibilities/requirements/benefits jsonb,
employment_type enum, workplace_type enum, city, country_code char(2),
experience_level enum, salary_min/salary_max integer nullable,
salary_currency char(3) nullable, salary_period enum nullable,
salary_is_public bool default false, positions_count smallint default 1,
status enum(draft,published,paused,closed,archived),
published_at/closes_at nullable, is_featured bool,
views_count/applications_count integer default 0, seo jsonb, softDeletes.
CHECK constraint: salary_max >= salary_min when both present.

pipeline_stages: key unique, name jsonb, color, sort_order, is_default,
is_terminal, outcome enum nullable. Seed: applied, screening, interview,
offer, hired, rejected.

job_applications: job_posting_id cascade, pipeline_stage_id restrictOnDelete,
form_submission_id nullable, first_name, last_name, email, phone nullable,
cover_letter text nullable, portfolio_url/linkedin_url nullable, locale char(2),
source nullable, ai_score smallint nullable, ai_summary jsonb nullable,
rating smallint nullable, is_read bool, applied_at, softDeletes.
UNIQUE (job_posting_id, email).
NO cv_path column — CVs go through Media Library.

stage_transitions: job_application_id cascade, from_stage_id nullable,
to_stage_id, user_id nullable, note text nullable, created_at ONLY (no updated_at).

application_notes: job_application_id cascade, user_id, body, is_pinned, timestamps.

redirects: from unique, to, status_code smallint default 301, locale nullable,
hits_count, last_hit_at.
redirect_misses: path, referrer, user_agent, hits_count, last_seen_at.
```

### P-1.6 · Models
```
Read .agent/skills/model.md.

Create all models with relationships, casts, translatable arrays, and enums
in app/Enums/.

Add scopeVisibleTo to JobPosting and JobApplication using the department
scoping pattern in the skill file. The final `whereRaw('1 = 0')` deny-by-default
branch is required — do not replace it with returning the unscoped query.

Add a JobPostingObserver that flushes the 'careers' cache tag on save.
```

### P-1.7 · Policies
```
Read .agent/skills/policy-testing.md.

Create a policy for every model.

JobApplicationPolicy must have a separate viewPii() ability, distinct from view().
Both call the same private inScope() helper.

Register all policies. Verify with `php artisan about`.
```

### P-1.8 · Policy tests — the most important task in Phase 1
```
Read .agent/skills/policy-testing.md.

Write Pest feature tests covering EVERY denial in this matrix.
Each ✘ and each ◐ is one test asserting 403 or an empty result set.

Roles: ceo, manager (department-scoped), hr, it

- ceo:     content view/publish/delete ✔ | create ✘ | applications+viewPii ✔ | users ✘
- manager: content create/update/publish ◐ | jobs create/update ◐ |
           applications view/viewPii/move ◐ | publish jobs ✘ | users ✘ | export ✘
- hr:      content edit ✘ | jobs full ✔ | applications full ✔ | users ✘
- it:      content edit ✘ | applications.view ✔ | applications.viewPii ✘ |
           users.manage ✔ | jobs create ✘

Include:
- A manager cannot open another department's application (403)
- A manager's application query returns zero rows from other departments
- A user with no role sees an empty system, not an error

Add factories with role helpers: User::factory()->manager(department: 'engineering')

Target: 100% coverage of authorisation paths. This is a release gate.
```

---

# Phase 2 — Admin

> Read `.agent/skills/filament-resource.md` before every prompt in this phase.

### P-2.1 · Panel
```
Read .agent/skills/filament-resource.md.

Configure the Filament panel: brand colour #000038, navigation groups
(Content, Careers, Forms, System), login page, and role-based navigation
visibility.
```

### P-2.2 · PageResource with the block builder
```
Read .agent/skills/filament-resource.md.

Create PageResource with a Builder field on `blocks` containing 16 block types:
hero_cinematic, hero_interior, case_reel, stat_row, process_triptych,
capability_scroll, logo_marquee, testimonials, character_loop, posts_grid,
jobs_list, faq, form, cta_band, rich_text, media_full.

Each block's schema holds only its own content. NEVER store a section number —
it is computed from position at render time.

Bilingual tabs (English / العربية) for title, slug, excerpt, and seo.
```

### P-2.3 · Content resources
```
Read .agent/skills/filament-resource.md.

Create PostResource, CategoryResource, TagResource, ProjectResource,
and MenuResource (with a nested item repeater supporting polymorphic
linkable targets: Page, Post, Project, JobPosting, or external URL).

All bilingual. All with policy-gated actions.
```

### P-2.4 · JobPostingResource
```
Read .agent/skills/filament-resource.md.

Create JobPostingResource:
- Bilingual tabs for all content fields
- Auto-generate reference_code on create: {DEPT}-{YEAR}-{SEQ}, e.g. DEV-2026-014
- Salary fields with a salary_is_public toggle
- Status transitions: draft → published → paused → closed → archived
- A preview action opening the public URL in the selected locale
- Publishing is blocked if any Arabic required field is empty

Override getEloquentQuery() with visibleTo() so managers see only their departments.
```

### P-2.5 · JobApplicationResource with redaction
```
Read .agent/skills/filament-resource.md and .agent/skills/policy-testing.md.

Create JobApplicationResource.

CRITICAL: override getEloquentQuery() with visibleTo(). Without it, filters,
exports, and counts leak other departments even when columns are hidden.

Columns: display_name (accessor that returns "Candidate #{id}" when the viewer
lacks applications.viewPii), stage badge, ai_score sortable, applied_at.

Email and phone columns visible only with applications.viewPii.
CV download action visible only when can('viewPii', $record), served via a
15-minute signed URL, and logged with actor and IP.
```

### P-2.6 · Kanban board
```
Read .agent/skills/filament-resource.md.

Create a custom Filament page: Applications Board.

One column per pipeline_stage, ordered by sort_order, coloured by stage colour.
Cards show candidate name (redacted per permission), reference code, ai_score.
Drag between columns writes a stage_transitions row with actor, from, to, timestamp.

Dragging requires the applications.move permission AND department scope.
Respect visibleTo() on the underlying query.
```

### P-2.7 · FormResource
```
Read .agent/skills/filament-resource.md and .agent/skills/form-engine.md.

Create FormResource. A repeater builds form_fields: key, type, bilingual
label/placeholder/help_text, options, validation rules, width, conditional logic,
sort order.

The `rules` field stores a Laravel validation array — it is the single source of
truth for both client and server validation. Validate that field keys are unique
per form.

Create SubmissionResource: read-only, filterable, CSV export gated on
submissions.export.
```

### P-2.8 · System resources and dashboard
```
Read .agent/skills/filament-resource.md.

Create UserResource (roles, department assignment, managed departments,
activate/deactivate), RedirectResource, a redirect-miss review screen,
settings pages, and an activity log viewer.

Dashboard widgets: applications this week, hiring funnel by stage,
form submissions this week, recent activity.

All widgets respect role scope — a manager's funnel shows only their departments.
```

### P-2.9 · Admin access tests
```
Read .agent/skills/policy-testing.md.

Write feature tests asserting each role sees exactly what the matrix permits
in the admin panel:
- Resources absent from navigation return 403 on direct URL
- An IT user's application list renders "Candidate #" and never a real name
- A manager's application list contains zero rows from other departments
- A manager's CSV export contains zero rows from other departments
```

---

# Phase 3 — Design system

> Read `.agent/skills/rtl.md` and `.agent/skills/ssr-animation.md` before this phase.

### P-3.1 · Inertia and SSR
```
Configure Inertia with React 19 and TypeScript. Set up Vite for both client
and SSR builds.

Verify `php artisan inertia:start-ssr` runs and renders a test page in Node.

This must work before any component is written. Do not proceed until it does.
```

### P-3.2 · Layout and shared props
```
Read .agent/skills/rtl.md and .agent/skills/inertia-page.md.

Create HandleInertiaRequests sharing: locale, direction, active locales,
menus, settings, auth (user + roles + permissions), flash.
Wrap expensive props in closures.

Create AppLayout setting dir and lang on <html> from the direction prop,
with header, footer, and a language switcher preserving the current route.

Create the useCan() hook and a <Can do="permission"> component.
Add a comment on <Can> stating it is presentation only, never protection.
```

### P-3.3 · Design tokens
```
Read .agent/skills/rtl.md.

Port the Tailwind config from the existing Next.js repo: colours, type scale,
fonts, spacing. Brand navy is #000038.

Build shared UI: Button, Link (with a trailing ↗ that mirrors to ↖ in RTL),
SectionEyebrow (NN / LABEL), Card, Container.

Use logical properties only. Verify the ESLint rule catches a physical one.
```

### P-3.4 · Animation utilities
```
Read .agent/skills/ssr-animation.md.

Create animation utilities using useGSAP from @gsap/react with automatic
scoped cleanup.

Every hook must:
- Register plugins inside useEffect, never at module scope
- Return early when prefers-reduced-motion is set
- Revert all timelines and ScrollTriggers on unmount

Build: useScrollReveal, useStaggerIn, useDragRotate.
```

### P-3.5 · SSR smoke test
```
Read .agent/skills/ssr-animation.md.

Create tests/ssr-smoke.mjs that imports and renders every component in
resources/js/Pages in Node and fails on any reference to window, document,
or IntersectionObserver.

Add it to the frontend CI job after npm run build:ssr.

Verify it catches a deliberately broken component, then fix that component.
```

### P-3.6 · Generated types
```
Configure spatie/laravel-typescript-transformer to emit
resources/js/types/generated.d.ts from Eloquent models and enums.

Add the generation step to CI so a model change that breaks a React prop
fails the build rather than failing at runtime.
```

---

# Phase 4 — Pages & blocks

> Read `.agent/skills/block-component.md` before every prompt.
> Add `.agent/skills/ssr-animation.md` for any animated block.

### P-4.1 · Block infrastructure
```
Read .agent/skills/block-component.md.

Create BlockResolver (PHP): takes the blocks jsonb array, eager-loads all
referenced models in ONE query per type, returns hydrated data.
A case_reel storing [3,7,12] must not produce three queries.

Create the React registry and <Blocks> renderer. Pass index (position + 1)
to every block for section numbering. Return null for unknown block types —
never crash.
```

### P-4.2 · Hero and static blocks
```
Read .agent/skills/block-component.md and .agent/skills/ssr-animation.md.

Build: HeroCinematic (with the intro sequence, audio gated behind a
Sound on/Mute control), HeroInterior, StatRow, ProcessTriptych, CtaBand,
RichText, MediaFull.

Match the existing site's devices: numbered eyebrow, italic headline clause,
↗ link arrows.
```

### P-4.3 · Scroll-driven blocks
```
Read .agent/skills/block-component.md and .agent/skills/ssr-animation.md.

Build CaseReel (drag + auto-rotate) and CapabilityScroll (scroll-driven).

These are the highest-risk components for SSR. Every animation init inside
useGSAP with scope. Verify by navigating between pages five times and
confirming zero leaked GSAP instances.
```

### P-4.4 · Remaining blocks
```
Read .agent/skills/block-component.md.

Build: LogoMarquee, TestimonialReel, CharacterLoop, PostsGrid, JobsList,
FaqAccordion (emitting FAQPage JSON-LD), FormBlock.

JobsList must handle the empty state — when no vacancies are open it renders
the open-application call to action, with both copy variants read from settings.
```

### P-4.5 · Controllers and routes
```
Read .agent/skills/inertia-page.md.

Create PageController, PostController (index, show, category, tag),
ProjectController (index with filters, show).

Routes: /, /{slug}/, /blog/, /blog/{slug}/, /blog/category/{slug}/,
/portfolio/, /work/{slug}/

Controllers stay thin — retrieval and Inertia::render only. Output shaping
in API Resources.
```

### P-4.6 · SEO layer
```
Read .agent/skills/seo.md.

Create SeoService emitting per-route title, description, canonical, OG tags,
and hreflang pairs for en/ar.

Structured data: Organization sitewide, Article on posts, BreadcrumbList
on nested pages.

Generate sitemap.xml with hreflang alternates, robots.txt, and feed.xml.
```

### P-4.7 · URL middleware
```
Read .agent/skills/seo.md.

Create CanonicalUrlMiddleware enforcing ONE trailing-slash policy globally and
301-ing the other form. The existing site uses trailing slashes — match it.

Create HandleRedirects middleware checking the redirects table BEFORE any 404
renders. On a match, 301 and increment hits_count. On no match, log to
redirect_misses with the referrer, then let the 404 render.

Tests: both middlewares, both directions.
```

### P-4.8 · Caching
```
Read .agent/skills/model.md.

Add a cache layer: resolved Inertia props cached per URL and locale, tagged
by model type.

Invalidation happens ONLY in model observers, never scattered through
controllers or Filament actions.

Verify zero N+1 queries on every page using Telescope in a local environment.
```

---

# Phase 5 — Careers & forms

### P-5.1 · Careers public pages
```
Read .agent/skills/inertia-page.md and .agent/skills/rtl.md.

Create CareerController and the Careers/Index page: open positions grouped by
department, filterable by employment_type and workplace_type.

When no vacancies are open, render the open-application fallback instead.
Both copy variants come from settings, not hardcoded.

Create Careers/Show for vacancy detail, both locales.
```

### P-5.2 · JobPosting structured data
```
Read .agent/skills/seo.md.

Emit JobPosting JSON-LD on every published vacancy: title, description,
datePosted, validThrough, employmentType, hiringOrganization, jobLocation.

OMIT baseSalary entirely when salary_is_public is false — a null value fails
Google's validation.

Write a test asserting the emitted JSON matches the expected schema shape,
including the omission case.
```

### P-5.3 · DynamicForm component
```
Read .agent/skills/form-engine.md and .agent/skills/rtl.md.

Build <DynamicForm schema={fields} /> rendering all 13 field types from a
form_fields definition.

Use react-hook-form with a Zod schema generated from the same `rules` column
via buildZodSchema(). Client validation is UX only.

Support conditional logic — hidden fields are excluded from validation.
Support field widths (full/half/third) matching the site grid.
RTL correct in Arabic.
```

### P-5.4 · Server validation and submission
```
Read .agent/skills/form-engine.md.

Create SubmitFormRequest generating its rules from form_fields.rules —
the same source the client schema uses.

Create FormSubmissionService: store, email the notify list, fire the optional
webhook, apply retention.

Add honeypot handling (bots get HTTP 200 and a silent discard — never an error)
and throttle:5,1 rate limiting.

Test: a curl request that bypasses the browser entirely is still rejected.
```

### P-5.5 · Application submission
```
Read .agent/skills/form-engine.md and .agent/skills/policy-testing.md.

Create ApplicationController@store:
- Validate, including duplicate check on (job_posting_id, email)
- Store the CV via Media Library on a private 'cv' collection
- Create the application in the default pipeline stage with the applying locale
- Increment applications_count
- Send a locale-matched acknowledgement email
- Dispatch the n8n webhook to the QUEUE, not inline
- Redirect to /careers/thank-you/ so refresh cannot resubmit

CRITICAL: if the webhook fails, the application is STILL SAVED. The webhook
retries on the queue. Write a test proving an application survives an n8n outage.
```

### P-5.6 · n8n integration
```
Read .agent/skills/form-engine.md.

Create ScoreApplicationJob dispatching to n8n with a signed 15-minute CV URL
and the vacancy requirements.

Create a signed callback endpoint receiving ai_score (0-100) and ai_summary
{strengths[], gaps[], verdict}. Store model name, prompt version, and
scored_at alongside the score so results stay auditable when the prompt changes.

The score NEVER changes the pipeline stage. It orders the queue only.
A human makes every stage decision. Add a code comment saying so.
```

### P-5.7 · Scheduled jobs and emails
```
Create scheduled commands:
- Auto-close vacancies past closes_at
- Purge applications and submissions past their retention period

Create bilingual email templates: application acknowledgement, form notification.

Configure a transactional provider (Postmark or Resend). Do NOT use VPS SMTP —
shared IPs land candidate emails in spam.

Test: send to a real address and confirm inbox delivery, not spam.
```

---

# Phase 6 — Migration & launch

### P-6.1 · Redirect import
```
Read .agent/skills/seo.md.

Create a command: php artisan redirects:import {csv}

Reads url-inventory.csv and populates the redirects table.
Map every /rt-portfolio/{slug}/ to /work/{slug}/ with a 301.

Then create a command redirects:verify that requests every URL in the inventory
against the app and reports any returning an unplanned 404.
```

### P-6.2 · Content import
```
Create commands to import posts and projects from the existing Next.js content
into the database, preserving slugs exactly.

Slug preservation is not optional — a changed slug is a broken indexed URL.
```

### P-6.3 · SEO parity check
```
Read .agent/skills/seo.md.

Create a command: php artisan seo:parity {inventory.csv}

For every URL, compare the new site's title, meta description, canonical, and
OG tags against the frozen inventory. Output a diff report.

The launch gate is zero unexplained differences.
```

### P-6.4 · Deployment
```
Create a zero-downtime deploy script using a releases directory and a `current`
symlink swap.

Steps: clone, link shared .env and storage, composer install --no-dev,
npm ci + build + build:ssr, migrate --force, cache config/routes/views/events,
swap symlink, reload php-fpm, restart queue and SSR supervisor programs,
health check, rollback on failure.

Supervisor configs for: queue worker, inertia SSR.
Cron for schedule:run.
```

### P-6.5 · Monitoring
```
Install and configure Sentry for both PHP and JavaScript.

Add a /health endpoint checking database, Redis, and queue connectivity.

Configure alerts on: failed queue jobs, webhook failures, 5xx rate above
baseline, redirect_misses spiking, disk above 80%.

Trigger a test error from both PHP and JS and confirm both reach Sentry.
```

---

## Session compaction prompt

Run this whenever the context feels heavy, then start fresh.

```
Update .agent/state/STATE.md:
- What was completed this session
- What is in progress and its exact next step
- Any gotcha discovered that future sessions need
- Any environment or config change

Keep it under 40 lines. If a decision changed the architecture, append one
line to .agent/state/DECISIONS.md.

Do not summarise code. Only state.
```

## When Codex drifts

```
You used a pattern not defined in .agent/skills/. Either:
(a) rewrite it to match the existing skill file, or
(b) tell me which skill file should be updated and what line to add.

Do not introduce a third pattern.
```
