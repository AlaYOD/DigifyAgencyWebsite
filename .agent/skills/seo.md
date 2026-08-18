# Skill: SEO & structured data

## JobPosting — highest-value item in the project
Free, high-intent candidate traffic via Google Jobs.

```php
[
  '@context' => 'https://schema.org',
  '@type' => 'JobPosting',
  'title' => $job->title,
  'description' => $job->description,          // HTML allowed
  'datePosted' => $job->published_at->toIso8601String(),
  'validThrough' => $job->closes_at?->toIso8601String(),
  'employmentType' => $job->employment_type->schemaValue(),
  'hiringOrganization' => ['@type' => 'Organization', 'name' => 'Digify', 'sameAs' => config('app.url')],
  'jobLocation' => ['@type' => 'Place', 'address' => [
      '@type' => 'PostalAddress',
      'addressLocality' => $job->city,
      'addressCountry'  => $job->country_code,
  ]],
]
```
OMIT `baseSalary` entirely when `salary_is_public` is false. A null value fails validation.

Validate every change at Google's Rich Results Test.

## Other types
- `Organization` sitewide
- `Article` on posts
- `FAQPage` on FAQ blocks
- `BreadcrumbList` on nested pages

## hreflang — both directions
```html
<link rel="alternate" hreflang="en" href="https://digifyagency.co/careers/" />
<link rel="alternate" hreflang="ar" href="https://digifyagency.co/ar/careers/" />
<link rel="alternate" hreflang="x-default" href="https://digifyagency.co/careers/" />
```

## Canonical URLs
The existing site serves TRAILING SLASHES. Enforce one form globally in
middleware and 301 the other. Serving both is duplicate content.

## Redirects
Check the `redirects` table BEFORE returning any 404. Log unmatched paths to
`redirect_misses` so gaps surface within minutes, not months.

## Per-page meta
Every route emits: title, description, canonical, OG image, hreflang pair.
Editable per record via the `seo` jsonb column.
