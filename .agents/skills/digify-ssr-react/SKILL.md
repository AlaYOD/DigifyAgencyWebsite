---
name: digify-ssr-react
description: Guidelines for Inertia.js React 19 frontend development, SSR safety, logical CSS, GSAP animation cleanup, and RTL/bilingual handling.
---

# Digify SSR, React 19 & RTL Rules

## SSR Safety Rules
Inertia SSR renders components in Node. `window`, `document`, `localStorage`, and `IntersectionObserver` do not exist at module scope.
1. **Never reference browser APIs at module top-level**:
   - Wrap in `useEffect`, `useLayoutEffect` (with `typeof window !== 'undefined'` guard), or `useGSAP`.
2. **GSAP Cleanup**:
   - Use `@gsap/react` `useGSAP` hook with a container `ref` for scoped auto-reversion.
   - Respect `prefers-reduced-motion`.

## Logical CSS (RTL & LTR)
- ESLint bans physical spacing classes.
- Always use logical properties:
  - `ps-*` / `pe-*` (not `pl-*` / `pr-*`)
  - `ms-*` / `me-*` (not `ml-*` / `mr-*`)
  - `text-start` / `text-end` (not `text-left` / `text-right`)
  - `border-s` / `border-e` (not `border-l` / `border-r`)
  - `start-0` / `end-0` (not `left-0` / `right-0`)
- Mixed content: wrap phone numbers, emails, dates with `<bdi>` or `dir="ltr"`.

## Forms & Inertia Navigation
- Use Inertia's `router.post()` or `useForm` hook with `forceFormData: true` for file uploads.
- Never use direct `fetch` / `axios` calls to internal backend routes.
- Use `usePage().props` to access shared props (`locale`, `direction`, `locales`, `settings`, `flash`, `auth`).
