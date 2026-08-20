import type { BlockComponentProps } from './types';
import { nestedRecords, text } from './types';

export default function Testimonials({ props }: BlockComponentProps) {
    return <section className="space-y-8"><h2 className="text-4xl">{text(props, 'title')}</h2><div className="grid gap-5 md:grid-cols-2">{nestedRecords(props, 'items').map((item, index) => <figure key={index} className="rounded-3xl bg-brand-paper p-8"><blockquote className="text-xl leading-8 text-brand-navy">“{String(item.quote ?? '')}”</blockquote><figcaption className="mt-6 text-sm text-brand-muted"><strong className="text-brand-navy">{String(item.author ?? '')}</strong>{item.role ? ` · ${String(item.role)}` : ''}{item.company ? `, ${String(item.company)}` : ''}</figcaption></figure>)}</div></section>;
}
