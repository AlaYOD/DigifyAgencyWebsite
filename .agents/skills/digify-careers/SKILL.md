---
name: digify-careers
description: Guidelines and patterns for the Careers and Job Applications module (models, PII redaction, CV security, SEO JobPosting schema, applications board).
---

# Digify Careers & Application Workflow

## Key Models & Relationships
- `JobPosting`: Department relationship, translatable fields (`title`, `slug`, `summary`, `description`, `responsibilities`, `requirements`, `benefits`), enum casts, `reference_code` generation (`{DEPT}-{YEAR}-{SEQ}`).
- `JobApplication`: Attached to `JobPosting` and `PipelineStage`, handles candidate data, rating, AI screening score, and transitions.
- `PipelineStage`: Stages (`applied`, `screening`, `interview`, `offer`, `hired`, `rejected`), sort order, terminal flags, stage colors.
- `StageTransition`: Audit log of stage changes with user attribution and timestamps.
- `ApplicationNote`: Team notes on candidate applications.

## PII Security & Redaction
- Candidate personal identifiable information (name, email, phone, CV) is protected by the `applications.viewPii` permission.
- Without `applications.viewPii`:
  - Name is masked as `Candidate #{id}`.
  - Email and phone columns are hidden.
  - CV download actions are hidden.
- CV files are stored on the `private` disk via Spatie MediaLibrary.
- CV downloads use 15-minute temporary signed URLs via `JobApplicationCvController` and log an activity record with user ID and IP address.

## Structured Data (SEO)
- Published vacancy detail pages emit `JobPosting` JSON-LD schema.
- If `salary_is_public` is `false`, `baseSalary` must be completely omitted (null value invalidates Google Jobs validation).

## Scope Isolation
- `scopeVisibleTo(Builder $query, User $user)` ensures managers only query jobs/applications belonging to their managed departments.
- Unmatched roles hit `whereRaw('1 = 0')` (deny by default).
