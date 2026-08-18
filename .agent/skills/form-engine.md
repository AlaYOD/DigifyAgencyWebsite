# Skill: Dynamic forms

## The contract
`form_fields.rules` (jsonb) is the SINGLE source of truth. It generates BOTH
the client schema and the server rules. They cannot drift because they derive
from one record.

## Server — the security boundary
```php
class SubmitFormRequest extends FormRequest
{
    public function rules(): array
    {
        return $this->form->fields
            ->mapWithKeys(fn ($f) => ["data.{$f->key}" => $f->rules])
            ->all();
    }
}
```

## Client — UX only
```ts
export function buildZodSchema(fields: FormField[]) {
  const shape: Record<string, z.ZodTypeAny> = {};
  for (const f of fields) {
    let s: z.ZodTypeAny = z.string();
    if (f.rules.includes('email')) s = z.string().email();
    const max = f.rules.find(r => r.startsWith('max:'));
    if (max) s = (s as z.ZodString).max(Number(max.split(':')[1]));
    if (!f.rules.includes('required')) s = s.optional();
    shape[f.key] = s;
  }
  return z.object(shape);
}
```

## Field types
text · textarea · email · tel · number · date · select · multiselect · radio ·
checkbox · file · heading · paragraph

## Conditional logic
```json
{"field": "has_experience", "op": "=", "value": "yes"}
```
Hidden fields are excluded from validation on both sides.

## Spam handling
- Honeypot: a bot that fills it gets HTTP 200 and SILENT DISCARD.
  Never return an error — that teaches the bot.
- Rate limit: `throttle:5,1` per IP on every public form.

## CV uploads
- PDF/DOCX only, 10 MB max, MIME allowlist (not just extension)
- Private disk. Access ONLY via 15-minute signed URLs
- Every download logged with actor and IP
- No `cv_path` column — use Media Library so access runs through policies

## Failure rule
If the n8n webhook fails, THE APPLICATION IS STILL SAVED. The webhook retries
on the queue. Screening failure must never lose a candidate.
