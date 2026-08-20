Updated: 2026-08-20
Phase: CMS/admin implementation complete and running locally
Admin: Filament dashboard, bilingual Pages/Posts/Projects/Categories/Tags, Menus, Media, Forms, Submissions, Careers, Locales, Settings, Redirects/404 report, Activity log, and Users
Page builder: 16 registered bilingual blocks with media/content/form/job resolution and React SSR renderers
Form builder: 13 field types, stored rules, conditional logic, widths, CAPTCHA, email/webhook delivery, redirect/inline success, CSV export, and retention purge
Content: JSONB translations/slugs/SEO, publishing states, soft deletes, department scopes, activity logs, revisions, and restore actions
Public: bilingual CMS pages, posts, projects, standalone/embedded forms, menus, careers/open application, sitemap, managed redirects, and RTL server HTML
Careers: fixed and assigned dynamic forms, private CV storage, duplicate defense, pipeline board, notes/ratings, signed CV access, encrypted mail jobs, and PII redaction
Security: policies registered for every authorised CMS model; roleless and department scopes deny by default; no raw IP storage; sanitized rich HTML
Operations: submission retention command scheduled daily; local app/queue/SSR/Postgres/Redis services configured in Docker
Data: additive migrations applied to the active local database; roles, locales, starter forms/homepage/menu, and demo users seeded idempotently
Local URL: http://127.0.0.1:8000/admin
Demo access: ceo@digify.test, it@digify.test, editor@digify.test (password: password)
Quality: Pest 51 passed / 188 assertions; PHPStan, Pint, TypeScript, and ESLint clean; client and SSR production builds pass
Browser QA: public EN/AR, localized navigation, dynamic form success, CEO page builder, IT form builder, and site settings verified
External setup: production mail, Turnstile, and optional n8n/LLM screening still require provider credentials and privacy approval
Gotchas: PHP/Composer/Artisan/npm run inside the app container; Node 24 is pinned; Windows bind-mount requests are slow in the CLI dev server
Audit reference: output/reports/digify-cms-sdd-implementation-audit-2026-08-20.md is the pre-implementation baseline, not current status
