# Digify CMS Platform
## Implementation Playbook — A to Z

**Version** 1.0 · **Companion to** `DIGIFY-CMS-SDD-v1.0` (what to build)
**This document covers** how to build it: prerequisites, skills, environment, task breakdown, runbooks, and handover.

---

## Contents

- [Part 0 — Before day one](#part-0--before-day-one)
- [Part 1 — Skills required](#part-1--skills-required)
- [Part 2 — Tools & accounts](#part-2--tools--accounts)
- [Part 3 — Environment setup](#part-3--environment-setup)
- [Part 4 — Repository & conventions](#part-4--repository--conventions)
- [Part 5 — Phase 1: Foundation](#part-5--phase-1-foundation)
- [Part 6 — Phase 2: Admin](#part-6--phase-2-admin)
- [Part 7 — Phase 3: Design system port](#part-7--phase-3-design-system-port)
- [Part 8 — Phase 4: Pages & blocks](#part-8--phase-4-pages--blocks)
- [Part 9 — Phase 5: Careers & forms](#part-9--phase-5-careers--forms)
- [Part 10 — Phase 6: Migration & launch](#part-10--phase-6-migration--launch)
- [Part 11 — Testing plan](#part-11--testing-plan)
- [Part 12 — CI/CD](#part-12--cicd)
- [Part 13 — Cutover runbook](#part-13--cutover-runbook)
- [Part 14 — First 30 days](#part-14--first-30-days)
- [Part 15 — Handover & training](#part-15--handover--training)
- [Part 16 — Execution risks](#part-16--execution-risks)

---

# Part 0 — Before day one

Nothing below starts until these are closed. Each blocks real work.

## 0.1 Decisions

| # | Decision | Blocks | Owner |
|---|---|---|---|
| D-1 | Approval to begin | Everything | CEO |
| D-2 | Hosting — VPS or stay on Vercel | Phase 3 onward, not Phase 1 | CEO + you |
| D-3 | Fifth `content_editor` role — yes or no | Phase 1 (schema and seeder) | CEO |
| D-4 | AI screening — third-party model, self-hosted, or none | Phase 5 | CEO |
| D-5 | Editorial review workflow — yes or no | Phase 1 (content models) | You |
| D-6 | Arabic URL pattern — confirm what the existing site uses | Phase 4 | You |

> **D-3 and D-5 look small and are not.** Both touch every content model and every policy. Adding either after Phase 2 means revisiting work already done and tested.

## 0.2 The URL freeze — do this first

Before writing a single line of code. This artefact is the foundation of the entire migration and cannot be reconstructed after cutover.

```bash
# Crawl the live site
npx @screaming-frog/cli --headless --crawl https://digifyagency.co \
  --output-folder ./audit --export-tabs "Internal:All"

# Or free alternative
wget --spider --recursive --no-verbose \
     --output-file=crawl.log https://digifyagency.co
grep -oP '(?<=URL:)\S+' crawl.log | sort -u > urls.txt
```

Supplement with:
- Google Search Console → Pages → all indexed URLs (export)
- `sitemap.xml` from the live site
- Any URLs used in printed material, email signatures, or ad campaigns

**Output:** `url-inventory.csv` with columns: `url`, `status`, `title`, `meta_description`, `canonical`, `h1`, `indexed`.

Commit it to the repository. It is the source of truth for redirects and the checklist for SEO parity.

## 0.3 Access needed

- [ ] Existing Next.js repository — read access minimum, ideally write
- [ ] Domain registrar / DNS control
- [ ] Vercel project (for rollback and for the performance comparison)
- [ ] Google Search Console for `digifyagency.co`
- [ ] Google Analytics property
- [ ] VPS provider account
- [ ] Existing n8n instance credentials
- [ ] Brand assets: logo files, fonts (licensed), character illustrations at source resolution

## 0.4 Baseline measurements

Capture these before anything changes. Without a baseline you cannot prove the migration was neutral or positive.

| Metric | Source | Record |
|---|---|---|
| Organic sessions, last 90 days | Analytics | |
| Indexed page count | Search Console | |
| Average position, top 20 queries | Search Console | |
| Core Web Vitals — LCP, CLS, INP | Search Console + PageSpeed | |
| Lighthouse mobile, on `/`, `/careers/`, `/blog/` | PageSpeed Insights | |
| TTFB from Ramallah, Amman, Dubai, Frankfurt | WebPageTest | |

That last row is the input to decision D-2. Do not decide hosting on preference.

---

# Part 1 — Skills required

## 1.1 Assessment

Rated against what this build actually demands.

| Skill | Needed for | Level required | Likely status | Gap-closing |
|---|---|---|---|---|
| **Laravel 12** | Everything backend | Advanced | Strong | — |
| **Eloquent — scopes, policies, observers** | Permissions, caching | Advanced | Likely strong | — |
| **Filament 4** | Entire admin | Intermediate–Advanced | **Probable gap** | 3–5 days on docs + one throwaway resource |
| **Inertia.js** | The whole front-end bridge | Intermediate | **Probable gap** | 1–2 days; concepts are small |
| **Inertia SSR** | SEO, and the animation port | Advanced | **Likely gap** | Hardest single item — see §1.2 |
| **React 19** | Public site | Advanced | Strong | — |
| **TypeScript** | Typed props, generated types | Intermediate | Possibly partial | Ongoing; start strict and stay strict |
| **PostgreSQL** | JSONB, GIN, partial indexes | Intermediate | **Partial if coming from MySQL** | 1 day on JSONB and indexing specifically |
| **Pest / PHPUnit** | Policy coverage — non-negotiable | Intermediate | **Probable gap** | 2–3 days; the highest-value gap to close |
| **GSAP / scroll animation** | Porting the cinematic sections | Intermediate | Partial | Existing code is the reference |
| **Tailwind — logical properties** | RTL correctness | Advanced | Strong | — |
| **Arabic/RTL web** | Both locales | Advanced | Strong | — |
| **SEO — structured data, hreflang** | Google Jobs, migration parity | Intermediate | Partial | 1 day on JobPosting + hreflang |
| **GitHub Actions** | CI pipeline | Intermediate | Partial | 1 day |
| **Linux VPS — nginx, supervisor, certs** | Deployment | Intermediate | Strong | — |
| **n8n** | AI screening | Intermediate | Strong | — |
| **Meilisearch** | Search — deferred | Beginner | Gap | Defer; not needed for v1 |

## 1.2 The honest gap assessment

**The largest risk is not a framework. It is testing discipline.**

Filament and Inertia are learnable in a week. Writing 100% policy coverage on a system holding candidates' personal data is a habit, not a skill — and it is the one thing that, if skipped, produces a failure nobody notices until it matters.

The second-largest is **Inertia SSR combined with animation porting**. This is genuinely difficult and specific to this project:

- Components that reference `window`, `document`, or `IntersectionObserver` crash in Node during server rendering
- GSAP timelines and ScrollTriggers leak across Inertia page transitions if not cleaned up
- Neither failure appears in local development with SSR off

Budget real time for it in Phase 3 rather than discovering it in Phase 4.

## 1.3 Learning order — before Phase 1

| Day | Focus | Output |
|---|---|---|
| 1 | Filament 4 — panels, resources, forms, tables | One resource built against a throwaway model |
| 2 | Filament — relation managers, custom pages, policies | A working Kanban prototype |
| 3 | Inertia — shared props, forms, partial reloads | A two-page demo with a working form |
| 4 | Inertia SSR — build, run, and deliberately break it | A component that crashes SSR, then fixed |
| 5 | Pest — feature tests, actingAs, assertForbidden | Three passing policy tests |

Five days. Compressible to three if you already know Filament. **Do not skip day 4** — it is the one that saves a week later.

## 1.4 If a second developer joins

| Developer | Owns | From |
|---|---|---|
| Backend | Schema, admin, policies, careers, forms, integrations | Phase 1 |
| Front end | Design port, blocks, public pages, SSR safety | Phase 3 |

The clean seam is the block contract (§4.5 of the SDD). Once the props shape is agreed, the two sides move independently.

---

# Part 2 — Tools & accounts

| Category | Item | Notes |
|---|---|---|
| Repository | GitHub private repo | Branch protection on `main` |
| CI | GitHub Actions | Free tier is sufficient |
| Error tracking | Sentry | Free tier covers this volume |
| Uptime | Better Stack / UptimeRobot | Free tier |
| Transactional email | Postmark / Resend | **Not VPS SMTP** — see §14.6 of the SDD |
| Hosting | Hetzner / Hostinger VPS, EU region | Pending D-2 |
| CDN | Cloudflare | Free tier; required if leaving Vercel |
| Backups | Provider snapshots + off-server dumps | Two locations minimum |
| Design | Existing Figma | Reference only, no new design |
| Local | Docker Desktop or OrbStack | |

**Minimum production VPS:** 4 vCPU, 8 GB RAM, 80 GB NVMe. Redis, Postgres, Meilisearch, and queue workers on one box is comfortable at this scale.

---

# Part 3 — Environment setup

## 3.1 Local

```bash
composer create-project laravel/laravel digify-cms
cd digify-cms

composer require \
  filament/filament:"^4.0" \
  inertiajs/inertia-laravel \
  spatie/laravel-translatable \
  spatie/laravel-medialibrary \
  spatie/laravel-permission \
  spatie/laravel-activitylog \
  spatie/laravel-settings \
  spatie/laravel-typescript-transformer

composer require --dev \
  pestphp/pest --with-all-dependencies \
  larastan/larastan \
  laravel/pint

php artisan filament:install --panels

npm i @inertiajs/react react react-dom \
      react-hook-form @hookform/resolvers zod \
      @gsap/react gsap lucide-react clsx tailwind-merge
npm i -D @vitejs/plugin-react typescript @types/react @types/node \
         tailwindcss @tailwindcss/vite eslint prettier

npx shadcn@latest init
```

## 3.2 Docker

`compose.yml`:

```yaml
services:
  app:
    build: .
    volumes: ['.:/var/www']
    ports: ['8000:8000']
    depends_on: [pgsql, redis]

  pgsql:
    image: postgres:16-alpine
    environment:
      POSTGRES_DB: digify
      POSTGRES_USER: digify
      POSTGRES_PASSWORD: secret
    volumes: ['pgdata:/var/lib/postgresql/data']
    ports: ['5432:5432']

  redis:
    image: redis:7-alpine
    ports: ['6379:6379']

volumes: { pgdata: }
```

## 3.3 Environment variables

```env
APP_NAME="Digify CMS"
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=pgsql
DB_DATABASE=digify

CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

FILAMENT_FILESYSTEM_DISK=public
MEDIA_DISK=public
CV_DISK=private              # never public

MAIL_MAILER=postmark
POSTMARK_TOKEN=

N8N_WEBHOOK_URL=
N8N_CALLBACK_SECRET=         # signs the score write-back

SENTRY_LARAVEL_DSN=
```

## 3.4 Production provisioning

```
Ubuntu 24.04 LTS
├── nginx                      reverse proxy, TLS via certbot
├── PHP 8.3-FPM                opcache + JIT enabled
├── PostgreSQL 16
├── Redis 7
├── Node 20                    for the SSR process
├── supervisor
│   ├── queue worker           php artisan queue:work --tries=3
│   ├── horizon                (if Redis queue)
│   └── inertia-ssr            php artisan inertia:start-ssr
└── cron                       * * * * * php artisan schedule:run
```

**Deploy layout** — atomic releases with a symlink swap:

```
/var/www/digify/
├── releases/20260901120000/
├── shared/
│   ├── .env
│   └── storage/
└── current -> releases/20260901120000
```

## 3.5 Three environments

| | Local | Staging | Production |
|---|---|---|---|
| Purpose | Development | Client review, migration rehearsal | Live |
| Data | Seeded | Production copy, **PII scrubbed** | Real |
| Indexing | n/a | `noindex` + basic auth | Indexed |
| Debug | On | On | **Off** |
| Telescope | On | On | **Off** |

> Staging must be `noindex` and password-protected before it contains a single real page. A staging site indexed by Google competes with production for the same content.

---

# Part 4 — Repository & conventions

## 4.1 Branching

```
main          protected · always deployable · tagged releases
  └── dev     integration
       └── feat/T-042-job-posting-resource
       └── fix/T-088-rtl-arrow-mirror
```

Branch names carry the task ID. Commits reference it: `feat(careers): add JobPosting resource (T-042)`.

## 4.2 Definition of done

A task is not done until **all** are true:

- [ ] Code merged to `dev`
- [ ] Tests written and passing — policy tests where authorisation is touched
- [ ] `pint`, `phpstan`, `tsc`, `eslint` all clean
- [ ] Both locales checked if user-facing
- [ ] RTL verified if it renders anything
- [ ] Reviewed by a second person, or self-reviewed against a written checklist if solo
- [ ] SDD requirement ID referenced in the PR description

## 4.3 `AGENTS.md`

Place at the repository root. This is what keeps coding agents from inventing a new pattern every session.

```markdown
# Digify CMS — Agent Instructions

## Stack
Laravel 12 · Filament 4 · Inertia.js · React 19 · TypeScript · PostgreSQL 16 · Tailwind

## Hard rules
- The careers table is `job_postings`, NEVER `jobs` (Laravel's queue owns `jobs`)
- The careers model is `JobPosting`, NEVER `Job`
- Applications are `job_applications`
- Translatable fields are JSONB via spatie/laravel-translatable
- NEVER use `pl-` `pr-` `ml-` `mr-` `text-left` `text-right`.
  Use logical properties: `ps-` `pe-` `ms-` `me-` `text-start` `text-end`
- NEVER use localStorage or sessionStorage
- Every animation init goes inside useEffect. SSR runs in Node — no `window`
- Use `useGSAP` from @gsap/react so cleanup is automatic
- Every model with authorisation needs a Policy AND a test asserting denial
- Filament resources with scoped data MUST override getEloquentQuery()

## Structure
app/Filament/Resources/       admin
app/Http/Controllers/Web/     returns Inertia::render only
app/Policies/                 one per model
resources/js/Pages/           Inertia pages
resources/js/Components/blocks/  block library, registry in index.ts

## Conventions
- Controllers are thin. Business logic goes in app/Services/
- Validation lives in Form Requests, never inline in controllers
- Output shaping lives in API Resources, never in controllers
- Cache invalidation happens in Model Observers, never scattered in actions

## Testing
- Pest. Feature tests over unit tests.
- Every ✘ and ◐ in the permission matrix is a test.
```

## 4.4 Quality gates

```json
// package.json
"scripts": {
  "check": "tsc --noEmit && eslint . && prettier --check ."
}
```

```bash
composer pint && composer phpstan && composer test
```

---

# Part 5 — Phase 1: Foundation

**Goal:** a working database and permission system. No interface yet.
**Estimate:** 1.5 weeks · **Blocked by:** D-3, D-5

| ID | Task | Est |
|---|---|---|
| T-001 | Project scaffold, Docker, packages installed, CI skeleton green | 0.5d |
| T-002 | `AGENTS.md`, `README`, Pint/PHPStan/ESLint config | 0.5d |
| T-003 | Migration: `locales` + seeder (en, ar) | 0.25d |
| T-004 | Migration: `departments` | 0.25d |
| T-005 | Migration: users extension + `department_user` pivot | 0.5d |
| T-006 | Permission tables, full permission list, role seeder | 1d |
| T-007 | Media library install, conversions (thumb/card/hero, WebP) | 0.5d |
| T-008 | Settings classes — general, contact, social, seo, careers | 0.5d |
| T-009 | Migrations: categories, tags, posts, post_tag | 0.5d |
| T-010 | Migration: pages (+ partial unique homepage index) | 0.5d |
| T-011 | Migration: projects | 0.25d |
| T-012 | Migrations: menus, menu_items | 0.5d |
| T-013 | Migrations: forms, form_fields, form_submissions | 0.5d |
| T-014 | Migration: job_postings | 0.5d |
| T-015 | Migrations: pipeline_stages (+ seeder), job_applications | 0.75d |
| T-016 | Migrations: stage_transitions, application_notes | 0.25d |
| T-017 | Migrations: redirects, redirect_misses | 0.25d |
| T-018 | Activity log install and configuration | 0.25d |
| T-019 | All models: relationships, casts, translatable traits | 1d |
| T-020 | `ScopedToDepartments` trait + `visibleTo` scopes | 0.5d |
| T-021 | All policies — every model, every action | 1d |
| T-022 | **Policy test suite — every matrix cell** | 1.5d |
| T-023 | Factories and a realistic demo seeder | 0.5d |

**Exit criteria**

- [ ] `php artisan migrate:fresh --seed` runs clean on Postgres
- [ ] Every `✘` and `◐` in the permission matrix has a passing test
- [ ] `phpstan` passes at level 6
- [ ] No table named `jobs` outside Laravel's queue

> **T-022 is the phase.** Everything else is typing. If schedule pressure appears, cut scope elsewhere — never here. This is the only thing standing between a permission bug and candidates' personal data.

---

# Part 6 — Phase 2: Admin

**Goal:** staff can operate the entire system. Public site does not exist yet.
**Estimate:** 2 weeks

| ID | Task | Est |
|---|---|---|
| T-030 | Filament panel: branding, navigation groups, auth | 0.5d |
| T-031 | `PageResource` — Builder field with all 16 block types | 2d |
| T-032 | `PostResource` + Category and Tag resources | 1d |
| T-033 | `ProjectResource` | 0.5d |
| T-034 | `MenuResource` with nested item repeater | 0.75d |
| T-035 | `DepartmentResource`, `PipelineStageResource` | 0.5d |
| T-036 | `JobPostingResource` — bilingual tabs, all fields, preview action | 1.5d |
| T-037 | `JobApplicationResource` — table, filters, redacted columns | 1d |
| T-038 | **Applications Kanban page** — drag between stages, writes transitions | 1.5d |
| T-039 | Application detail: CV viewer, notes, rating, AI summary panel | 1d |
| T-040 | `FormResource` — repeater builds fields, rules, conditional logic | 1.5d |
| T-041 | `SubmissionResource` — read-only, filters, CSV export | 0.5d |
| T-042 | `UserResource` — roles, department assignment, activate/deactivate | 0.75d |
| T-043 | `RedirectResource` + redirect-miss review screen | 0.5d |
| T-044 | Settings pages | 0.5d |
| T-045 | Dashboard widgets: applications this week, funnel, submissions, activity | 1d |
| T-046 | `getEloquentQuery()` overrides on every scoped resource | 0.5d |
| T-047 | Feature tests: each role sees only what the matrix permits | 1d |
| T-048 | Activity log viewer | 0.25d |

**Exit criteria**

- [ ] HR can create, translate, and publish a vacancy end to end
- [ ] A Manager's application list contains only their departments
- [ ] An IT user sees redacted names and receives 403 on a CV URL
- [ ] Dragging a card writes a `stage_transitions` row
- [ ] A form built in the admin persists correct `rules` JSON

---

# Part 7 — Phase 3: Design system port

**Goal:** the visual foundation and SSR safety. The hardest phase technically.
**Estimate:** 1.5 weeks · **Blocked by:** D-2

| ID | Task | Est |
|---|---|---|
| T-060 | Extract Tailwind config, fonts, and tokens from the Next.js repo | 0.5d |
| T-061 | Vite + React + TypeScript + Inertia wiring | 0.5d |
| T-062 | **Inertia SSR configured, building, and running** | 0.75d |
| T-063 | `AppLayout` — `dir`/`lang` from locale, header, footer, language switch | 1d |
| T-064 | `HandleInertiaRequests` — locale, menus, settings, auth, flash | 0.5d |
| T-065 | ESLint rule banning physical CSS properties | 0.25d |
| T-066 | shadcn/ui base components, RTL-corrected | 0.75d |
| T-067 | Port shared UI: buttons, links with `↗`, eyebrows, cards | 1d |
| T-068 | **Port animation utilities with `useGSAP` and SSR guards** | 1.5d |
| T-069 | `prefers-reduced-motion` handling across all animated components | 0.5d |
| T-070 | TypeScript transformer — generated types from Eloquent models | 0.5d |
| T-071 | `useCan()` hook and `<Can>` component | 0.25d |
| T-072 | **SSR smoke test in CI** — render every page in Node, fail on browser API | 0.5d |

**Exit criteria**

- [ ] `php artisan inertia:start-ssr` renders every page without error
- [ ] Navigating between pages five times leaks zero GSAP instances
- [ ] `eslint` fails on a deliberately introduced `pl-4`
- [ ] Arabic layout mirrors correctly, including the `↗` arrow
- [ ] Reduced-motion skips the intro sequence entirely

> **T-068 and T-072 are the phase.** Every later phase depends on animations that survive server rendering. Discovering this in Phase 4 costs a week.

---

# Part 8 — Phase 4: Pages & blocks

**Goal:** the public site, driven from the database.
**Estimate:** 2.5 weeks

| ID | Task | Est |
|---|---|---|
| T-080 | `BlockResolver` — hydrates relations, eliminates N+1 | 0.75d |
| T-081 | Block registry and `<Blocks>` renderer with index numbering | 0.5d |
| T-082 | `hero_cinematic` + `hero_interior` | 1d |
| T-083 | `case_reel` — drag and auto-rotate | 1.5d |
| T-084 | `capability_scroll` — scroll-driven | 1.5d |
| T-085 | `process_triptych`, `stat_row`, `character_loop` | 1d |
| T-086 | `logo_marquee`, `testimonials` | 0.75d |
| T-087 | `posts_grid`, `jobs_list` | 0.75d |
| T-088 | `faq` (+ FAQPage JSON-LD), `cta_band` | 0.75d |
| T-089 | `rich_text`, `media_full`, `form` block | 0.5d |
| T-090 | `PageController` + `Pages/Show` + service template | 0.75d |
| T-091 | `PostController` — index, show, category, tag archives | 1d |
| T-092 | `ProjectController` — index with filters, detail | 0.75d |
| T-093 | Homepage assembled entirely from blocks | 1d |
| T-094 | SEO service — meta, canonical, OG, hreflang on every route | 0.75d |
| T-095 | Structured data: Organization, Article, BreadcrumbList | 0.5d |
| T-096 | `sitemap.xml`, `robots.txt`, `feed.xml` | 0.5d |
| T-097 | Canonical URL middleware (trailing-slash policy) | 0.25d |
| T-098 | Redirect middleware + miss logging | 0.5d |
| T-099 | Cache layer + observer-driven invalidation | 0.75d |
| T-100 | 404 and 500 pages, both locales | 0.25d |

**Exit criteria**

- [ ] Homepage renders from the database and is visually identical to the current site
- [ ] Reordering blocks in the admin renumbers eyebrows correctly
- [ ] Lighthouse mobile ≥ 90 on `/` and `/blog/`
- [ ] Every route emits correct canonical and hreflang
- [ ] Zero N+1 queries on any page (verify with Telescope in staging)

---

# Part 9 — Phase 5: Careers & forms

**Goal:** the module with the clearest business return.
**Estimate:** 2 weeks · **Blocked by:** D-4

| ID | Task | Est |
|---|---|---|
| T-110 | `CareerController` — index with department grouping and filters | 0.75d |
| T-111 | `Careers/Index` — open positions and the empty-state fallback | 1d |
| T-112 | `Careers/Show` — vacancy detail, both locales | 0.75d |
| T-113 | **`JobPosting` JSON-LD** — validated against Rich Results Test | 0.5d |
| T-114 | `DynamicForm` React component — all 13 field types | 2d |
| T-115 | Zod schema generator from the `rules` column | 0.75d |
| T-116 | Conditional field logic | 0.75d |
| T-117 | `FormRequest` generator from the same `rules` column | 0.5d |
| T-118 | Application submission: store, CV upload, dedupe, acknowledge | 1d |
| T-119 | `Careers/Apply` + `open-application` + `thank-you` | 0.75d |
| T-120 | Honeypot, rate limiting, spam scoring | 0.5d |
| T-121 | Form submission handler: store, notify, webhook | 0.75d |
| T-122 | Standalone `/forms/{key}/` route | 0.25d |
| T-123 | **n8n webhook out + signed score write-back endpoint** | 1d |
| T-124 | Email templates — acknowledgement and notification, both locales | 0.75d |
| T-125 | Scheduled jobs: auto-close vacancies, purge by retention | 0.5d |
| T-126 | Feature tests: apply, duplicate, closed vacancy, **n8n outage** | 1d |

**Exit criteria**

- [ ] An application submitted publicly appears on the Kanban board
- [ ] `JobPosting` markup passes Google's Rich Results Test with zero errors
- [ ] A duplicate application is refused with its reference code
- [ ] **An application submitted while n8n is down is still saved and scored later**
- [ ] Server validation rejects a request sent with curl, bypassing the browser
- [ ] Acknowledgement email arrives in the inbox, not spam

---

# Part 10 — Phase 6: Migration & launch

**Goal:** live, with no ranking loss.
**Estimate:** 1.5 weeks

| ID | Task | Est |
|---|---|---|
| T-140 | Import posts from the existing site | 0.5d |
| T-141 | Import projects and case studies | 0.5d |
| T-142 | Build every static page as blocks | 1.5d |
| T-143 | Arabic content entry and review | 1d |
| T-144 | Populate `redirects` from `url-inventory.csv` | 0.5d |
| T-145 | `/rt-portfolio/*` → `/work/*` mapping | 0.25d |
| T-146 | **Crawl diff: staging vs frozen inventory** | 0.5d |
| T-147 | SEO parity check — title, description, canonical, OG, page by page | 1d |
| T-148 | Production provisioning, TLS, supervisor, cron | 0.75d |
| T-149 | Deploy pipeline, backups, monitoring, Sentry | 0.75d |
| T-150 | Load test and Lighthouse on production hardware | 0.5d |
| T-151 | **Staff training + recorded walkthroughs** | 1d |
| T-152 | Cutover (Part 13) | 0.25d |

**Exit criteria**

- [ ] Zero unplanned 404s across the entire frozen inventory
- [ ] A database restore has been performed, not merely configured
- [ ] Staff have entered real content themselves without assistance
- [ ] Rollback to Vercel tested and confirmed working

---

# Part 11 — Testing plan

## 11.1 Coverage targets

| Area | Target | Rationale |
|---|---|---|
| Policies and authorisation | **100%** | Personal data. Non-negotiable |
| Form validation | 100% | Security boundary |
| Careers flows | 90% | Business-critical |
| Block rendering | Smoke only | Visual, better caught by review |
| Admin CRUD | Happy path | Filament is already tested upstream |

## 11.2 Structure

```
tests/
├── Feature/
│   ├── Policies/
│   │   ├── JobApplicationPolicyTest.php    every role × every action
│   │   ├── PagePolicyTest.php
│   │   └── DepartmentScopingTest.php       row-level isolation
│   ├── Careers/
│   │   ├── ApplicationSubmissionTest.php
│   │   ├── DuplicateApplicationTest.php
│   │   ├── ClosedVacancyTest.php
│   │   └── WebhookFailureTest.php          n8n down → still saved
│   ├── Forms/
│   │   ├── DynamicValidationTest.php
│   │   ├── HoneypotTest.php
│   │   └── RateLimitTest.php
│   ├── Seo/
│   │   ├── JobPostingSchemaTest.php
│   │   └── HreflangTest.php
│   └── Migration/
│       └── RedirectCoverageTest.php        asserts against url-inventory.csv
└── Unit/
    ├── BlockResolverTest.php
    └── ValidationRuleGeneratorTest.php
```

## 11.3 The matrix-to-test rule

```php
it('denies a manager access to another department\'s application', function () {
    $manager = User::factory()->manager(department: 'engineering')->create();
    $application = JobApplication::factory()->forDepartment('design')->create();

    actingAs($manager)
        ->get("/admin/job-applications/{$application->id}")
        ->assertForbidden();
});

it('redacts candidate names from IT users', function () {
    $it = User::factory()->it()->create();
    $application = JobApplication::factory()->create(['first_name' => 'Layla']);

    actingAs($it)
        ->get('/admin/job-applications')
        ->assertDontSee('Layla')
        ->assertSee('Candidate #');
});
```

Every `✘` and every `◐` in the permission matrix becomes one of these. No exceptions.

## 11.4 Manual QA checklist — before every release

- [ ] Every page, both locales, mobile viewport
- [ ] Arabic layout mirrors correctly, including directional icons
- [ ] Keyboard navigation reaches every interactive element with visible focus
- [ ] Reduced-motion disables all animation
- [ ] Forms fail correctly with invalid input, both locales
- [ ] CV upload rejects a wrong file type and an oversized file
- [ ] A test email lands in the inbox, not spam

---

# Part 12 — CI/CD

## 12.1 Pipeline

```yaml
name: CI
on: [push, pull_request]

jobs:
  backend:
    runs-on: ubuntu-latest
    services:
      postgres:
        image: postgres:16
        env: { POSTGRES_PASSWORD: secret, POSTGRES_DB: testing }
        options: >-
          --health-cmd pg_isready --health-interval 10s --health-retries 5
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with: { php-version: '8.3', coverage: xdebug }
      - run: composer install --no-interaction --prefer-dist
      - run: vendor/bin/pint --test
      - run: vendor/bin/phpstan analyse --memory-limit=1G
      - run: vendor/bin/pest --coverage --min=80
      - run: composer audit

  frontend:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: actions/setup-node@v4
        with: { node-version: 20, cache: npm }
      - run: npm ci
      - run: npx tsc --noEmit
      - run: npx eslint .
      - run: npm run build
      - run: npm run build:ssr
      - run: node tests/ssr-smoke.mjs      # renders every page in Node
      - run: npm audit --audit-level=high
```

## 12.2 Deployment

```bash
#!/usr/bin/env bash
set -euo pipefail

RELEASE="/var/www/digify/releases/$(date +%Y%m%d%H%M%S)"
git clone --depth 1 --branch "$1" "$REPO" "$RELEASE"
cd "$RELEASE"

ln -sfn /var/www/digify/shared/.env .env
ln -sfn /var/www/digify/shared/storage storage

composer install --no-dev --optimize-autoloader
npm ci && npm run build && npm run build:ssr

php artisan migrate --force
php artisan config:cache route:cache view:cache event:cache
php artisan storage:link

ln -sfn "$RELEASE" /var/www/digify/current

sudo systemctl reload php8.3-fpm
sudo supervisorctl restart digify-queue digify-ssr

php artisan up
curl -fsS https://digifyagency.co/health || { echo "HEALTH FAILED"; exit 1; }
```

**Rollback:** repoint the `current` symlink at the previous release and restart. Seconds, not minutes.

---

# Part 13 — Cutover runbook

Pick a low-traffic window. Sunday early morning suits this audience.

## T-7 days
- [ ] Staging matches production content exactly
- [ ] Crawl diff clean — zero unplanned 404s
- [ ] Staff trained and have entered real content themselves
- [ ] Backups verified by performing an actual restore
- [ ] DNS TTL lowered to 300 seconds

## T-1 day
- [ ] Final content sync
- [ ] Full production database backup
- [ ] Redirect table verified once more against the frozen inventory
- [ ] Rollback procedure walked through with whoever is on call

## T-0

| Time | Action | Verify |
|---|---|---|
| 00:00 | Freeze content edits on the old site | Announced to staff |
| 00:10 | Final content sync to production | Row counts match |
| 00:20 | Deploy, run migrations, warm caches | Health endpoint 200 |
| 00:30 | Switch DNS to the new host | `dig` from three networks |
| 00:45 | Smoke test 20 key URLs | All 200 or intended 301 |
| 01:00 | Verify TLS, `sitemap.xml`, `robots.txt` | Valid certificate chain |
| 01:15 | Submit sitemap in Search Console | Accepted |
| 01:30 | Test a real application submission | Appears on the board, email received |
| 02:00 | Monitor error rate and redirect misses | Baseline holding |

## T+1 to T+14
- [ ] **Vercel deployment stays live and reachable**
- [ ] Search Console coverage checked daily
- [ ] Redirect-miss log reviewed daily, gaps patched same day
- [ ] Analytics compared against the pre-migration baseline

## Rollback triggers

Revert DNS immediately if any of these occur:

- 5xx rate above 1% for more than five minutes
- Applications failing to save
- Widespread 404s across indexed URLs
- TLS or certificate failure

Rollback is a DNS change. Content entered into the new system is not lost — it lives in the database and republishes on the second attempt.

---

# Part 14 — First 30 days

| Window | Focus |
|---|---|
| Days 1–3 | Daily Search Console and error-log review. Patch redirect misses same-day |
| Days 4–7 | First real vacancy published by HR unassisted. Watch for friction, not bugs |
| Week 2 | Compare Core Web Vitals against baseline. Fix regressions before they compound |
| Week 3 | First candidate hired through the pipeline end to end. Validate the funnel report |
| Week 4 | Retrospective; log deferred items as a v1.1 backlog |

**Watch specifically for**

- Redirect misses trending up rather than down
- Queue jobs failing silently
- Emails landing in spam — check bounce and complaint webhooks
- Staff quietly reverting to old habits (asking a developer instead of using the admin). This is the adoption risk, and it shows up as silence

---

# Part 15 — Handover & training

## 15.1 Sessions

| Audience | Duration | Covers |
|---|---|---|
| HR | 90 min | Create a vacancy, publish, manage the board, download CVs, notes |
| Marketing | 90 min | Pages and blocks, articles, scheduling, media, forms |
| CEO | 30 min | Dashboard, reports, activity log |
| IT | 60 min | Users and roles, redirects, settings, backups, deploy, logs |

**Format:** they drive, you watch. If they cannot complete the task without you touching the keyboard, the training has not worked.

## 15.2 Artefacts

- [ ] Screen recording per module, 5–10 minutes each
- [ ] One-page quick reference per role
- [ ] `README` with local setup in under ten commands
- [ ] Architecture decision records for D-1 through D-6
- [ ] Runbook: deploy, rollback, restore a backup, rotate a secret
- [ ] Credentials handed to IT through a password manager, never email

## 15.3 Support taper

| Period | Arrangement |
|---|---|
| Weeks 1–2 | Same-day response, daily check-in |
| Weeks 3–4 | Same-day response, weekly check-in |
| Month 2+ | Agreed maintenance arrangement |

---

# Part 16 — Execution risks

Distinct from the product risks in §16 of the SDD. These are risks to *delivering*, not to the system.

| ID | Risk | Mitigation |
|---|---|---|
| E-01 | SSR animation port takes far longer than estimated | Dedicated tasks in Phase 3; SSR smoke test in CI; treat T-068 as the phase gate |
| E-02 | Filament learning curve slows Phase 2 | Five days of prep before Phase 1 (§1.3); build a throwaway resource first |
| E-03 | Client work interrupts the build | This is the most likely cause of slippage. Block calendar time explicitly, or accept a longer elapsed timeline honestly |
| E-04 | Content entry stalls in Phase 6 | Start Arabic content during Phase 4, not Phase 6. It is the longest lead item |
| E-05 | Decisions D-3 or D-5 arrive late | Both block Phase 1. Escalate if not closed before the start date |
| E-06 | Scope grows mid-build | SDD requirement IDs are the contract. Anything unlisted is a change request with its own estimate |
| E-07 | Solo developer, no reviewer | Arrange a second reader even part-time. Self-review against a written checklist is a weak substitute but better than none |
| E-08 | Testing gets cut under pressure | Policy tests are a release gate, not a nice-to-have. Cut features instead |

---

## Quick reference — estimate summary

| Phase | Weeks | Cumulative |
|---|---|---|
| 0 · Prep and learning | 1 | 1 |
| 1 · Foundation | 1.5 | 2.5 |
| 2 · Admin | 2 | 4.5 |
| 3 · Design port | 1.5 | 6 |
| 4 · Pages & blocks | 2.5 | 8.5 |
| 5 · Careers & forms | 2 | 10.5 |
| 6 · Migration & launch | 1.5 | **12** |

**12 weeks including the preparation week**, one developer, sequential and uninterrupted.

The SDD quotes 11 weeks because it excludes preparation. Both figures are estimates. Neither survives contact with concurrent client work — if this is being built alongside other projects, double the elapsed time and say so up front rather than explaining it later.

### Fastest path to value

Phases 0, 1, 2, and 5 alone deliver a complete careers and applications system in roughly **seven weeks**, running against the current website with no migration risk. Everything else can follow.
