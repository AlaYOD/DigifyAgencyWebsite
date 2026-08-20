# Digify CMS SDD v1.1 - Implementation Audit

**Audit date:** 20 August 2026  
**Requirements source:** `C:\Users\hp\Downloads\Digify-CMS-SDD-v1.1.pdf`  
**Repository audited:** `D:\DigifyAgencyWebsite`  
**Document reviewed:** all 54 pages, including tables, diagrams, operational scenarios, acceptance criteria, open decisions, and Appendix A.

## 1. Interpretation and audit method

The PDF is an internal, "For approval" design and requirements document. Its statements were treated as requirements and design intent to evaluate, not as instructions to execute. The open decisions and sprint plan were also treated as planning material, not authorization to deploy, migrate data, contact services, or change production.

The audit used four evidence levels:

1. **Source evidence:** migrations, models, policies, controllers, Filament resources, React pages, configuration, deployment files, and tests.
2. **Automated verification:** Pint, PHPStan, Pest, TypeScript, ESLint, and the SSR smoke test.
3. **Runtime verification:** route inventory, migration status, live table existence, scheduler status, SSR health, and HTTP responses from the local Docker environment.
4. **Absence checks:** repository-wide searches for the required content, forms, redirect, settings, search, activity-log, and migration modules.

Status definitions:

- **Implemented:** the requirement has working code and meaningful verification.
- **Partial:** meaningful code exists, but a required path, safeguard, integration, validation, test, or runtime condition is missing.
- **Unimplemented:** no substantive implementation exists for the requirement.

## 2. Executive assessment

The repository is a **careers-first MVP foundation**, not the full Digify CMS platform described by the SDD.

| Area | Implemented | Partial | Unimplemented | Assessment |
|---|---:|---:|---:|---|
| Content, FR-C-01..15 | 0 | 0 | 15 | Module absent |
| Careers, FR-J-01..20 | 10 | 6 | 4 | Strong MVP, not release-complete |
| Forms, FR-F-01..14 | 0 | 2 | 12 | Dynamic engine absent |
| System, FR-S-01..11 | 0 | 7 | 4 | Foundation only |
| **All functional requirements** | **10** | **15** | **35** | **16.7% fully implemented; 41.7% implemented or partial** |

Non-functional requirements have 1 fully implemented, 3 partial, and 9 unimplemented or unverified items. None of the ten operational scenarios is complete end-to-end because every implemented careers scenario still depends on missing activity logging, editable settings, SSR/RTL behavior, forms, or n8n behavior.

### Overall verdict

- **Implemented core:** Laravel/PostgreSQL foundation, four-role seed, careers schema, policies and department scoping for the main careers models, Filament vacancy/application UI, Kanban stage moves, bilingual careers data, public careers routes, private CV handling in code, duplicate prevention, candidate/HR email, expiry scheduling, React/Inertia foundation, design tokens/fonts, and page-level SSR-safe hooks/tests.
- **Not implemented:** the content CMS, block library/page builder, dynamic forms engine, n8n/AI scoring, retention purge, redirects and 404 discovery, sitemap/search/settings admin, content/activity/revision tables, observability, complete CI/CD, content migration, SEO parity, cutover, and rollback proof.
- **Not production-ready today:** the live database is behind the code, actual HTTP SSR is not rendering, required legacy/open-application routes return 404, admin screens contain a runtime class error, authorization coverage is incomplete, and the PHP quality gates fail.

## 3. What is implemented

### 3.1 Platform foundation

- Laravel 12, Filament 4, Inertia, React 19, TypeScript, PostgreSQL 16, Redis 7, queue and SSR containers are present (`composer.json`, `package.json`, `compose.yml`).
- The critical naming rule is followed: careers use `job_postings` and `JobPosting`; applications use `job_applications` and `JobApplication` (`database/migrations/2026_08_18_000005_create_job_postings_table.php:12`, `database/migrations/2026_08_18_000007_create_job_applications_table.php:12`).
- Locale, department, user-extension, manager-department pivot, permission, careers, queue, cache, and media migrations exist.
- EN/AR translatable careers fields use PostgreSQL JSONB and Spatie Translatable (`app/Models/JobPosting.php:27`, `app/Models/Department.php:12`).
- Salary ordering and AI score range are database CHECK constraints (`database/migrations/2026_08_18_000005_create_job_postings_table.php:52`, `database/migrations/2026_08_18_000007_create_job_applications_table.php:39`).
- Explicit fillable lists are used on the implemented models.

### 3.2 Roles, policies, and scoping

- Four roles and named permissions are seeded (`database/seeders/RolePermissionSeeder.php`).
- `applications.viewPii` is separate from `applications.view`.
- `JobPosting` and `JobApplication` implement deny-by-default `visibleTo()` scopes (`app/Models/JobPosting.php:111`, `app/Models/JobApplication.php:72`).
- Their Filament resources override `getEloquentQuery()` (`app/Filament/Resources/JobPostings/JobPostingResource.php:37`, `app/Filament/Resources/JobApplications/JobApplicationResource.php:32`).
- Tests verify manager department isolation, roleless denial, IT CV denial, CEO/HR PII access, and immutable transition denial.

### 3.3 Careers administration and ATS

- Vacancy administration includes bilingual fields, department, job attributes, salary controls, publishing state, filters, close/publish actions, and generated reference codes.
- Applications have a scoped table, redacted name display, filters, CSV export for CEO/HR, CV action, stage-change action, notes relation manager, and rating field.
- The Kanban board is scoped, redacts names for IT, supports stage moves, and writes append-only `stage_transitions` (`app/Filament/Pages/ApplicationsBoard.php`).
- Pipeline stages are seeded as Applied, Screening, Interview, Offer, Hired, and Rejected.

### 3.4 Public careers and applications

- EN and AR careers index, vacancy, application, and thank-you routes exist (`routes/web.php:14-26`).
- Careers data is localized, grouped by department, and filterable by employment/workplace type (`app/Http/Controllers/Web/CareerController.php`).
- Vacancy resources produce canonical/hreflang metadata and JobPosting JSON-LD, with salary omitted when not public (`app/Http/Resources/CareerPostingResource.php:42-105`).
- Public application needs no account, uses server validation, a honeypot, 5/min/IP route throttling, private Media Library collection, duplicate checking, normalized email, default stage assignment, counter increment, and PRG thank-you redirect.
- Candidate acknowledgement and HR notification are queued; the candidate subject/body follows the application locale.
- The hourly `careers:close-expired` command is scheduled and tested.

### 3.5 Front-end and SSR foundation

- Brand tokens, Montserrat and FF Shamel fonts, logical Tailwind utilities, and EN/AR font switching are present (`resources/css/app.css`).
- Reusable GSAP hooks initialize inside `useGSAP`, guard server execution, scope effects, and consult reduced-motion preference (`resources/js/hooks`).
- TypeScript and ESLint pass, including the physical-direction utility restriction.
- The page-level SSR smoke test renders all four current React pages without a browser-global crash.

## 4. Critical gaps and defects

These are higher priority than adding new modules because they undermine existing careers functionality.

### 4.1 Live database is behind the code

`php artisan migrate:status` shows the media migration and email-normalization migration as pending. Runtime table checks show `media = false` and `activity_log = false`.

Impact:

- A real application upload reaches `$application->addMedia(...)` (`app/Http/Controllers/Web/ApplicationController.php:72`) but cannot persist a media record in the current live database.
- CV audit logging calls `activity()` (`app/Http/Controllers/Admin/JobApplicationCvController.php:18`) but the required table has no repository migration and does not exist in the live schema.
- Isolated tests pass because they run all migrations against the test database; that does not prove the current app database is ready.

### 4.2 Two Filament forms import a nonexistent Tabs class

`DepartmentForm` and `PipelineStageForm` import `Filament\Forms\Components\Tabs`; the installed Filament version has `Filament\Schemas\Components\Tabs` and does not have the imported class.

Affected files:

- `app/Filament/Resources/Departments/Schemas/DepartmentForm.php:5`
- `app/Filament/Resources/PipelineStages/Schemas/PipelineStageForm.php:6`

The tests do not instantiate these screens, so the error is not caught by the passing Pest suite.

### 4.3 Department authorization is internally contradictory

`DepartmentResource::canAccess()` admits HR (`app/Filament/Resources/Departments/DepartmentResource.php:25`), but `DepartmentPolicy` requires `users.manage` for every action (`app/Policies/DepartmentPolicy.php:10-33`), a permission granted to IT, not HR. The SDD assigns department and pipeline management to HR. Depending on the Filament authorization path, HR sees a navigation item but is denied actions, while IT owns the policy ability.

### 4.4 IT redaction does not cover all candidate PII

The candidate-name/email/phone section is hidden when PII permission is absent, but `cover_letter`, `portfolio_url`, and `linkedin_url` are outside that guard and are displayed to any user who can open the application (`app/Filament/Resources/JobApplications/Schemas/JobApplicationForm.php:23-28`). IT has `applications.view`, so this violates the SDD's "IT cannot read candidate personal details" boundary.

In addition, application mail failures log the recipient email (`app/Http/Controllers/Web/ApplicationController.php:127-131`), contrary to the SDD rule prohibiting unredacted PII in error logs.

### 4.5 SSR health passes, but actual HTTP SSR fails

`php artisan inertia:check-ssr` reports the SSR service running, and `tests/ssr-smoke.mjs` passes. Real HTTP responses for both `/careers/` and `/ar/careers/` still contain `<div id="app"></div>` with no rendered careers markup. The Arabic response has `<html lang="ar">` but no `dir="rtl"`; direction is applied later in `useEffect` (`resources/js/Layouts/AppLayout.tsx:17-20`).

Consequences:

- The current public pages are client-rendered, not proven SSR-rendered.
- Arabic direction is incorrect before hydration or when JavaScript fails.
- The SDD's SSR, indexing, and first-response RTL requirements are not met.

### 4.6 Required URLs and SEO endpoints are missing

Runtime results:

- `/careers` correctly returns a 301 to `/careers/`.
- `/careers/open-application/` returns 404.
- `/rt-portfolio/example/` returns 404 rather than an intentional redirect.
- `/sitemap.xml` returns 404.

`EnsureTrailingSlash` only covers careers routes (`app/Http/Middleware/EnsureTrailingSlash.php`), not a global canonical URL format.

### 4.7 Authorization testing is incomplete

Policies exist for seven implemented authorized models, but dedicated denial tests exist only for JobPosting, JobApplication, and StageTransition, plus roleless checks. There are no policy test suites for Department, PipelineStage, ApplicationNote, or User. The SDD's requirement that every denied/scoped matrix cell have a passing 403/empty-result test is therefore not met.

### 4.8 Architecture layers are not followed consistently

The SDD requires thin controllers that do not query the database or own business operations. `CareerController` queries directly. `ApplicationController` performs deduplication, transactions, stage selection, application creation, media storage, counters, mail dispatch, and error logging. There is no `app/Services` implementation for the application workflow. This increases coupling and makes webhook/retry/retention work harder to add safely.

### 4.9 Privacy consent and retention are absent

The public application form has no candidate privacy/processing notice or consent field. There is no configurable application retention field, purge command/job, cascade-erasure service, or n8n/LLM disclosure implementation.

### 4.10 CI and static quality gates are not complete

Only a frontend workflow exists (`.github/workflows/frontend.yml`). It does not run PHP formatting, PHPStan, Pest, Composer audit, npm audit, migration checks, or deployment steps.

Current gate results are detailed in section 9.

## 5. Functional requirement traceability

### 5.1 Content - FR-C

| ID | Status | Evidence and remaining work |
|---|---|---|
| FR-C-01 | Unimplemented | No Page model/table/resource, publishing workflow, scheduler, or public Page controller. |
| FR-C-02 | Unimplemented | No `resources/js/Components/blocks`, block registry, builder field, or block resolver. |
| FR-C-03 | Unimplemented | No fixed content block library or server-side block validation. |
| FR-C-04 | Unimplemented | No block rendering or computed numbered eyebrows. |
| FR-C-05 | Unimplemented | Careers fields are bilingual, but there are no content records or locale-tab content resources. |
| FR-C-06 | Unimplemented | Careers slugs are localized, but no content slug-change listener or automatic redirect exists. |
| FR-C-07 | Unimplemented | No hierarchical Page model or two-level URL resolver. |
| FR-C-08 | Unimplemented | No signed, non-indexable content preview route. |
| FR-C-09 | Unimplemented | Careers have `published_at`, but no scheduled content publication. |
| FR-C-10 | Unimplemented | Media package/migration exists, but no content conversions at 400/800/1920 WebP. |
| FR-C-11 | Unimplemented | No per-locale required alt-text validation for content blocks. |
| FR-C-12 | Unimplemented | No menus/menu_items schema, polymorphic links, or MenuResource. |
| FR-C-13 | Unimplemented | Activity package is referenced, but no activity-log migration/table or verified content audit trail. |
| FR-C-14 | Unimplemented | Careers models use soft deletes, but content models/restore/elevated hard-delete controls do not exist. |
| FR-C-15 | Unimplemented | No revisions table, snapshots, retention of 20, restore action, or tests. |

### 5.2 Careers - FR-J

| ID | Status | Evidence and remaining work |
|---|---|---|
| FR-J-01 | Partial | HR vacancy resource and EN/AR fields exist. Arabic title and description are checked on publish, but summary, responsibilities, requirements, and benefits are optional, so "full role detail in both locales" is not enforced. |
| FR-J-02 | Implemented | Unique reference code is generated transactionally by department/year; uniqueness and distinct-code tests exist (`app/Models/JobPosting.php:59-88`). |
| FR-J-03 | Implemented | Draft, published, paused, closed, and archived enum/database/UI states exist. A stricter allowed-transition graph is not defined, but the listed states are implemented. |
| FR-J-04 | Implemented | Hourly close-expired command, deployment scheduler entry, and expiry test pass. |
| FR-J-05 | Partial | JobPosting JSON-LD and salary gating exist. There is no automated schema test, Rich Results evidence, sitemap integration, or successful HTTP SSR proof. |
| FR-J-06 | Implemented | Careers query/filter/group behavior exists. Public UI labels remain hardcoded English on Arabic routes. |
| FR-J-07 | Partial | A no-vacancy panel exists, but copy is hardcoded, both variants are not editable, it does not link to a working general application, and `/careers/open-application/` returns 404. |
| FR-J-08 | Unimplemented | No Forms module, `form_id` column, default/custom form relation, or renderer. |
| FR-J-09 | Implemented | Public application routes require no account. |
| FR-J-10 | Partial | 10 MB limit, PDF/DOCX MIME check, private disk, and 15-minute signed policy-gated URL are implemented and tested. Original filename extension is not independently validated, and the live `media` table is missing. |
| FR-J-11 | Implemented | Email is normalized; application+email unique constraint, pre-check, race-condition handling, clear reference message, and tests exist. |
| FR-J-12 | Implemented | Submission selects the default stage and is visible through the scoped admin query/board; test exists. |
| FR-J-13 | Implemented | Moves append transition records; model blocks update/delete; board/table moves and tests exist. |
| FR-J-14 | Unimplemented | No n8n webhook job, signed CV payload, vacancy context, retry/backoff, failure recording, or alert. |
| FR-J-15 | Unimplemented | AI fields exist as placeholders only; UI explicitly states scoring is unavailable. No callback endpoint, signature, score service, audit metadata, or tests. |
| FR-J-16 | Partial | Filters cover stage, rating, date, and presence/absence of a score; sorting exists for score and applied date. Complete sorting/filtering by numeric score, rating, stage, and date is not implemented or tested. |
| FR-J-17 | Implemented | Notes are admin-only and have no candidate-facing response path. IT cannot open the notes relation, although separate IT PII leakage remains in the edit form. |
| FR-J-18 | Implemented | Queued acknowledgement follows application locale; EN/AR body and subject exist; mail persistence and localization tests pass. |
| FR-J-19 | Partial | Stage moves send no automatic email, satisfying the safety half. There is no deliberate terminal-stage email action or audit record. |
| FR-J-20 | Unimplemented | No application retention setting, purge scheduler/job, deletion audit, or CV/note/submission erasure workflow. |

### 5.3 Forms - FR-F

| ID | Status | Evidence and remaining work |
|---|---|---|
| FR-F-01 | Unimplemented | No Form/FormField models, migrations, Filament resources, or builder. |
| FR-F-02 | Unimplemented | No field-type enum or 13-type renderer. |
| FR-F-03 | Unimplemented | Current careers validation is duplicated in PHP and Zod; there is no single stored rules definition or generator. |
| FR-F-04 | Partial | The fixed careers form correctly treats FormRequest validation as authoritative, but the reusable Forms Engine does not exist. |
| FR-F-05 | Unimplemented | No conditional-logic schema, builder UI, or renderer. |
| FR-F-06 | Unimplemented | No field-width definition or grid renderer. |
| FR-F-07 | Unimplemented | No FormBlock or `/forms/{key}/` route. |
| FR-F-08 | Unimplemented | Careers submits/store/email only; there is no generic forms service, recipient configuration, or optional webhook. |
| FR-F-09 | Unimplemented | No email-only/non-storing mode. |
| FR-F-10 | Partial | Careers POST routes have a honeypot and 5/min/IP throttle. This is not applied to a generic public forms layer. |
| FR-F-11 | Unimplemented | No form_submissions metadata record for IP, user agent, referrer, locale, and UTM. |
| FR-F-12 | Unimplemented | No form-submission CSV export or locale-encoding test. |
| FR-F-13 | Unimplemented | No per-form inline/redirect success configuration. |
| FR-F-14 | Unimplemented | No per-form retention scheduler or purge. |

### 5.4 System - FR-S

| ID | Status | Evidence and remaining work |
|---|---|---|
| FR-S-01 | Partial | Four roles and many permissions exist, but the matrix is not fully represented/tested; DepartmentPolicy conflicts with HR ownership and no content/form/system policies exist. |
| FR-S-02 | Partial | Main careers scoping and IT CV denial work. IT can still see cover letters and profile URLs, and full cross-role isolation tests are absent. |
| FR-S-03 | Partial | Careers vacancies expose title, description, canonical, and hreflang. OG image/noindex are missing, and other page types do not exist. |
| FR-S-04 | Unimplemented | No sitemap route/generator, publish integration, or hreflang sitemap entries; runtime `/sitemap.xml` is 404. |
| FR-S-05 | Unimplemented | No redirects table, middleware, hit counter, admin resource, or redirect-before-404 behavior. |
| FR-S-06 | Unimplemented | No redirect_misses table, middleware, aggregation, or admin view. |
| FR-S-07 | Partial | Careers paths enforce trailing slashes, but policy is not global; the middleware regex is careers-specific. |
| FR-S-08 | Partial | JobPosting schema exists. Organization, Article, FAQPage, and BreadcrumbList coverage is absent. |
| FR-S-09 | Unimplemented | No search route/controller/page/index or database LIKE search service. |
| FR-S-10 | Partial | Settings package is installed, but no settings table/classes/admin. Shared site settings and HR recipients are hardcoded/config-based. |
| FR-S-11 | Partial | A daily PostgreSQL backup script and 14-day cleanup exist for staging. Media backup, off-server retention, monitoring, encryption, and a tested restore are absent. |

## 6. Non-functional requirements

| ID | Status | Evidence and gap |
|---|---|---|
| NFR-01 | Unimplemented | No measured mobile LCP for `/`, `/careers/`, or `/blog/`; two routes do not exist as CMS pages. |
| NFR-02 | Unimplemented | No Lighthouse run, threshold enforcement, or CI budget. |
| NFR-03 | Unimplemented | No page/props cache implementation or response-time measurement; current local careers request is uncached. |
| NFR-04 | Unimplemented | Basic semantic controls exist, but no WCAG 2.1 AA audit, automated accessibility test, or full site. |
| NFR-05 | Unimplemented | Native controls give a foundation, but complete keyboard/focus verification is absent. |
| NFR-06 | Partial | Reduced-motion guards exist in animation hooks, but the cinematic sections/audio intro do not exist and no behavioral test verifies every animation. |
| NFR-07 | Unimplemented | No uptime SLA evidence or external monitor. |
| NFR-08 | Partial | Daily DB backup script can support a 24-hour RPO, but media/off-server backup and restore proof are missing. |
| NFR-09 | Unimplemented | No cross-browser test matrix or results. |
| NFR-10 | Implemented in code/test | Private disk, single-file CV collection, policy-gated 15-minute signed URL, MIME/size tests. Live media migration remains a release blocker. |
| NFR-11 | Unimplemented | Normal session configuration exists; no explicit idle-timeout acceptance proof or destructive-action re-auth. |
| NFR-12 | Unimplemented | No visual mobile QA evidence for both locales. |
| NFR-13 | Partial | Careers tests are meaningful but do not cover 100% of policy/form-validation authorization paths. |

## 7. Operational scenario coverage

| Scenario | Status | Coverage and missing behavior |
|---|---|---|
| SC-01 HR publishes a vacancy | Partial | Vacancy admin, bilingual data, salary gating, reference code, publish/close and cache flush exist. Missing reliable department admin, bilingual preview, sitemap, working activity log, and complete locale validation. |
| SC-02 Candidate applies | Partial | Public no-account apply, RTL data prop, validation, private CV code, default stage, email, duplicate defense, thank-you PRG, and closed-vacancy checks exist. Missing privacy consent, n8n webhook/retry, live media migration, and server-rendered RTL HTML. |
| SC-03 AI pre-screening | Unimplemented | No webhook/callback/model integration or auditable score metadata workflow. |
| SC-04 Running the pipeline | Partial | Board, stage history, filters, notes, rating, CV download, manager scoping, and no automatic stage emails exist. Missing AI ordering, inline CV, true simultaneous-write handling, activity infrastructure, and complete IT redaction. |
| SC-05 Marketing publishes an article | Unimplemented | Content/post/scheduling/RSS/Article schema module absent. |
| SC-06 Launching a campaign form | Unimplemented | Dynamic forms module absent. |
| SC-07 Legacy URL | Unimplemented | No redirect middleware/tables/admin/miss logging; runtime legacy URL returns 404. |
| SC-08 Editor restructures homepage | Unimplemented | No homepage Page record, blocks, resolver, cache invalidation, renumbering, or revisions. |
| SC-09 Adding a language | Partial foundation | Locales are database rows and middleware reads active locale, but routes and language-switch logic hardcode EN/AR and there is no locale settings admin. Adding French still requires code/deployment. |
| SC-10 Access beyond role | Partial | IT application access, name redaction, CV 403, and inactive user panel denial exist. Missing full PII redaction, activity table, refusal logging, CEO notification after PII escalation, and monthly review workflow. |

## 8. Acceptance criteria assessment

### Content

All four criteria are unimplemented: bilingual block publishing/scheduling, block renumbering, automatic slug redirect, and revision restore.

### Careers

- **Evidenced:** a published vacancy is returned on EN/AR public routes; duplicate application rejection includes a reference.
- **Partial:** public submission appears in admin/Kanban, but no scored summary exists.
- **Not evidenced:** zero-error Google Rich Results validation; n8n outage followed by later successful scoring.

### Forms

The dynamic form builder acceptance criterion is unimplemented. Server-only validation and honeypot/rate limiting are evidenced only for the temporary fixed careers form.

### Permissions

- IT redacted-name display and direct CV 403 are tested.
- Manager export is likely constrained by the resource query, but has no export-scope test.
- Full matrix denial/scoping coverage is absent.
- CV audit logging code exists, but cannot be accepted without the activity table and a successful-download test.

### Migration

All migration acceptance criteria are unimplemented or unverified: frozen inventory coverage, metadata parity, bidirectional hreflang crawl, and two-week rollback.

### Operations

All four operations criteria remain unmet: full CI gate, PHP+JS error tracking test, completed database restore, and transactional deliverability test.

## 9. Verification results

| Check | Result | Detail |
|---|---|---|
| `vendor/bin/pint --test` | **Fail** | 21 files have style issues. No files were auto-formatted during this audit. |
| Documented `vendor/bin/phpstan` | **Fail / unconfigured** | Exits because no analysis path or PHPStan config is defined. |
| Explicit PHPStan level-6 app scan | **Fail** | 179 diagnostics after increasing memory to 512 MB. Many are amplified by missing Larastan/PHPStan configuration, but the gate is not clean and also detects real issues such as the bad Filament Tabs imports. |
| `vendor/bin/pest` | **Pass** | 45 tests, 109 assertions, 113.89 seconds. |
| `npx tsc --noEmit` | **Pass** | No TypeScript errors. |
| `npx eslint .` | **Pass** | No lint errors; logical-utility rule active. |
| `node tests/ssr-smoke.mjs` | **Pass** | Careers/Apply, Index, Show, and ThankYou render at component level. |
| Inertia SSR health | **Pass** | SSR service reports running. |
| Actual HTTP SSR markup | **Fail** | EN/AR careers responses contain empty `#app`; Arabic HTML has no server-side `dir="rtl"`. |
| Migration status | **Fail for current environment** | Media and email-normalization migrations pending. |
| Runtime required routes | **Fail** | Open application, legacy portfolio redirect, and sitemap return 404. |

## 10. Security and privacy assessment

### Implemented safeguards

- Session-authenticated Filament panel and inactive-user denial.
- Server-side FormRequest validation and CSRF through Laravel/Inertia.
- Database uniqueness and CHECK constraints.
- Private CV disk, MIME allowlist, size cap, signed URL, policy check, and expiration test.
- Main careers row scoping and roleless deny-by-default behavior.
- No localStorage/sessionStorage usage found.
- Candidate stage changes do not trigger automatic rejection or automatic mail.

### Missing or unsafe areas

- IT application detail leaks cover letter, portfolio URL, and LinkedIn URL.
- Mail failure logs may store a candidate email address.
- Activity logging is called but has no table/migration; refused and successful access are not comprehensively logged.
- Role changes do not notify the CEO; CEO assignment is not restricted to migration-only.
- Public application lacks consent/privacy text.
- No retention purge/right-of-erasure workflow.
- No n8n/LLM processing disclosure or processor decision.
- Upload validation lacks a separate original-extension check.
- No dependency audit CI, Sentry, backup restore test, or deliverability proof.
- Seeded demo accounts use a known password and should never be used in a production seed path (`database/seeders/DemoUserSeeder.php`).

## 11. Data-model comparison

### Implemented SDD tables

- `locales`
- `departments`
- `department_user`
- users extensions
- roles/permissions tables
- `job_postings`
- `pipeline_stages`
- `job_applications`
- `stage_transitions`
- `application_notes`
- queue/cache tables
- media migration exists but is pending in the current environment

### Missing SDD tables

- Content: `pages`, `posts`, `projects`, `categories`, `tags`, `post_tag`, `menus`, `menu_items`
- Forms: `forms`, `form_fields`, `form_submissions`
- System: `settings`, `redirects`, `redirect_misses`, `activity_log`
- Engineering improvement: `revisions`

### Careers schema deviations

- `job_postings.form_id` is missing.
- `job_applications.form_submission_id` is missing.
- Application retention configuration is missing.
- AI columns exist, but callback audit fields are only an intended JSON shape and are not enforced.
- Activity-log schema is missing even though models/controllers reference the package.

## 12. Information architecture and routes

Implemented public application routes are limited to `/`, EN/AR careers, vacancy, apply, and thank-you. `/` still returns the Laravel welcome Blade view (`routes/web.php:9-11`), not an Inertia CMS homepage.

Missing route groups include:

- pages and five service pages
- blog index/show/category/tag and RSS
- portfolio/work
- preserved open application
- standalone forms
- search
- privacy/terms CMS pages
- sitemap and dynamic robots/feed
- redirects and redirect-miss handling

Admin navigation groups are declared, but only Careers and Users have resources. The expected dashboard metrics, Content, Forms, Redirects, Settings, Media management, Activity Log, and Reports are absent.

## 13. Open decisions from the SDD

| Decision | SDD state | Repository state |
|---|---|---|
| Approval to begin | Pending in SDD | Work has begun; no explicit approval record found. |
| Hosting | Genuinely open | Repository follows a single Laravel Docker deployment with separate queue/SSR containers; no documented Vercel-vs-VPS measurement or final ADR. |
| Fifth `content_editor` role | Recommended | Not implemented; only CEO, Manager, HR, and IT are seeded. Content module is absent. |
| AI screening / third-party processing | Open | AI integration is absent; no processor/privacy decision record found. |
| Editorial review workflow | Recommendation to skip unless needed | No content workflow exists and no explicit decision record was found. |

The decision log records the naming, Inertia monolith, Filament admin, JSONB translations, and split PII permission decisions, but not these remaining decisions.

## 14. Recommended completion plan

### Priority 0 - make the existing careers MVP internally consistent

1. Apply/verify pending migrations in the intended environment and add the missing activity-log migration.
2. Fix the Department/PipelineStage Tabs imports and align DepartmentPolicy with HR ownership.
3. Close the IT PII leak and stop logging candidate email addresses on failures.
4. Add successful CV-download audit tests and policy denial suites for Department, PipelineStage, ApplicationNote, and User.
5. Configure PHPStan/Larastan, resolve diagnostics, run Pint, and add backend/audit jobs to CI.
6. Fix real HTTP SSR and emit `lang` and `dir` on server-rendered `<html>`.
7. Restore `/careers/open-application/` and make both fallback copy variants editable.

### Priority 1 - finish careers release scope

1. Add `form_id`/`form_submission_id` through the Forms Engine schema rather than another temporary form.
2. Implement queued n8n webhook/retry/callback, signed callback verification, audit metadata, and no-application-loss tests.
3. Complete score/rating/stage/date sorting/filtering and deliberate terminal-stage email actions.
4. Add privacy consent, configurable retention, scheduled purge, and erasure workflow.
5. Validate JobPosting JSON-LD against Google Rich Results, add sitemap integration, and test salary/remote/location variants.
6. Localize all public careers UI, validation, duplicate messages, filter labels, and application form labels.

### Priority 2 - build the Forms Engine

Implement FR-F-01..14: tables/models/policies/tests, 13 field types, one rules source, client/server generators, conditional logic, width, embeds/standalone routes, store/email/webhook service, email-only mode, metadata, CSV, success modes, and retention.

### Priority 3 - build Content and the block library

Implement FR-C-01..15 with content models, policies, Filament resources, block registry/resolver, previews, scheduled publishing, media conversions/alt validation, menus, activity, soft delete, revisions, public routes, and all 15 specified block types. Port the homepage last.

### Priority 4 - system, migration, and operations

1. Settings, redirects/misses, sitemap, structured data, search, activity log, reports, and dashboard.
2. Global canonical middleware and full route map.
3. Frozen URL inventory, redirect import, content migration, metadata/hreflang crawl, Lighthouse/mobile QA, Cloudflare decision, DNS cutover, and two-week rollback.
4. Sentry PHP/JS, uptime/queue/disk alerts, off-server database+media backups, successful restore, and mail deliverability tests.

## 15. Final conclusion

The project has delivered a meaningful and well-tested portion of the Appendix A careers MVP, especially the core schema, row scoping, application submission, duplicate protection, emails, expiry command, and Kanban stage history. It has **not** delivered the complete seven-day sprint outcome in the current environment because staging/live migration readiness, open application preservation, successful end-to-end deployment, activity logging, and HR handover are not evidenced.

Against the full SDD, most work remains: 35 of 60 functional requirements have no substantive implementation, 15 are partial, and only 10 are fully evidenced. The safest next move is to close the Priority 0 careers/security/runtime gaps before starting the Forms Engine, Content CMS, or migration work.
