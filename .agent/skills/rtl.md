# Skill: RTL & bilingual UI

Locales: `en` (LTR, default) · `ar` (RTL). Arabic is not an afterthought.

## Utilities — physical properties are a BUG
| Never | Always |
|---|---|
| `pl-4` `pr-4` | `ps-4` `pe-4` |
| `ml-2` `mr-2` | `ms-2` `me-2` |
| `text-left` `text-right` | `text-start` `text-end` |
| `border-l` `border-r` | `border-s` `border-e` |
| `rounded-l-lg` | `rounded-s-lg` |
| `left-0` `right-0` | `start-0` `end-0` |

Enforced by ESLint. A `pl-4` fails the build even if it looks correct in Arabic.

## Layout
```tsx
// AppLayout
<html lang={locale} dir={direction}>
```
`direction` comes from shared props, sourced from the `locales` table.

## Icons
- Directional (arrows, chevrons): `rtl:rotate-180`
- Non-directional (search, close, user): no mirroring
- The `↗` link arrow mirrors to `↖`

## Mixed content
Numbers, dates, phone numbers, emails, and code stay LTR inside RTL text:
```tsx
<bdi>+970 59 123 4567</bdi>
<span dir="ltr">2026-08-18</span>
```
Section numbers stay Western (`01`, not `٠١`) to match the design's rhythm.

## Fonts
`IBM Plex Sans Arabic` or `Rubik` — consistent metrics across Arabic and Latin.
Do not mix two families across scripts; the baseline shift is visible.

## Testing
Every page, both locales, mobile viewport. Arabic string lengths differ from
English and surface overflow bugs English does not.
