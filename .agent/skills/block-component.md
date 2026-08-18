# Skill: Block components

## Registry
```ts
// resources/js/Components/blocks/index.ts
const registry = {
  hero_cinematic: HeroCinematic,
  case_reel: CaseReel,
  // ...
} as const;

export function Blocks({ blocks }: { blocks: Block[] }) {
  return blocks.map((block, i) => {
    const C = registry[block.type as keyof typeof registry];
    if (!C) return null;                    // never crash on unknown block
    return <C key={block.id} {...block.data} index={i + 1} />;
  });
}
```

## Section numbering
`index` is passed from position, never stored:
```tsx
<div className="text-xs tracking-widest">
  <span className="text-amber-400">{String(index).padStart(2, '0')}</span>
  <span className="text-muted"> / {eyebrow}</span>
</div>
```
Reordering blocks renumbers automatically.

## BlockResolver — mandatory
Blocks store IDs, not objects. A `case_reel` holds `[3, 7, 12]`.
The resolver eager-loads them in one query. Without it: N+1 on every page load.

## Design devices (match the existing site)
- Eyebrow: `NN / LABEL` uppercase, number in accent colour
- Headlines carry an italic clause — stored as rich text with `<em>`, not separate fields
- Every outbound link ends `↗` (mirror to `↖` in RTL)
- Brand navy `#000038`

## Rules
- One component per block type, one file.
- Props typed from generated types (`resources/js/types/generated.d.ts`).
- Any block with animation must follow `ssr-animation.md`.
