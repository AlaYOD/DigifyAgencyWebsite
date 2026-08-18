# Skill: SSR-safe animation

Inertia SSR renders components in Node. `window`, `document`, and
`IntersectionObserver` DO NOT EXIST there. This is the single most common
cause of a broken build in this project.

## Rule 1 — nothing at module scope
```tsx
// WRONG — crashes SSR
gsap.registerPlugin(ScrollTrigger);
const w = window.innerWidth;

// RIGHT
useEffect(() => {
  gsap.registerPlugin(ScrollTrigger);
}, []);
```

## Rule 2 — use useGSAP for automatic cleanup
Inertia keeps the SPA alive across page changes. Without cleanup, timelines
and ScrollTriggers leak and stack on every navigation.

```tsx
import { useGSAP } from '@gsap/react';

const container = useRef<HTMLDivElement>(null);

useGSAP(() => {
  gsap.from('.reel-item', {
    opacity: 0, y: 40, stagger: 0.1,
    scrollTrigger: { trigger: container.current, start: 'top 80%' },
  });
}, { scope: container });     // reverts everything on unmount
```

## Rule 3 — reduced motion
```tsx
useGSAP(() => {
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
  // animation here
}, { scope: container });
```
The intro sequence with audio must skip straight to its resting state.

## Rule 4 — measuring layout
```tsx
useLayoutEffect(() => {
  if (typeof window === 'undefined') return;
  // measurement here
}, []);
```

## Verify
```bash
npm run build:ssr && php artisan inertia:start-ssr
node tests/ssr-smoke.mjs      # renders every page in Node
```
Navigate between pages five times and confirm zero leaked GSAP instances.
