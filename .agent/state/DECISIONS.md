# Decision log

Append-only. One line each. Newest at the bottom.

| Date | ID | Decision | Reason |
|---|---|---|---|
| — | D-A | Careers table is `job_postings`, model `JobPosting` | `jobs` collides with Laravel's queue table |
| — | D-B | Inertia monolith, not headless API | No second consumer of the data; API cost unjustified |
| — | D-C | Filament for admin despite React front end | ~60% of effort saved; admin UI is not visible value |
| — | D-D | Translations as JSONB, not a translations table | Postgres GIN indexes make it queryable; Filament supports it natively |
| — | D-E | `applications.viewPii` separate from `applications.view` | IT needs system access, not candidate personal data |
