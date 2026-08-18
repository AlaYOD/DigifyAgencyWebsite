Updated: 2026-08-18
Phase: sprint complete - hardening prepared, staging deployment blocked by missing server access
Done: P-1.1 schema, P-1.2 permissions/roles, P-1.5 careers schema, P-1.6 models/factories, P-1.7 policies, P-1.8 policy tests, P-2.1 Filament panel/resources, P-2.4 JobPostingResource, P-2.5 JobApplicationResource, P-2.6 Applications Kanban board, P-3.1 Inertia setup, P-3.2 RTL layout, P-5.1 careers index, P-5.2 careers show/SEO, P-5.5 application submission, P-5.7 transactional email/expiry jobs, and hardening tests/config
In progress: nothing
Next: provide staging host access, deploy, and perform verified backup restore
SSR: deliberately deferred; this sprint is client-only.
Gotchas: playbook must stay in docs/, never loaded; Node pinned to 24
Env notes: PHP, Composer, Artisan, and npm run only inside the app container; make is unavailable on this host, so use `docker compose exec app ...` and do not retry make; form_id and form_submission_id FKs deferred until forms tables exist
Deferred: SSR, design system port, forms engine, content module, n8n scoring, retention purging, content migration
