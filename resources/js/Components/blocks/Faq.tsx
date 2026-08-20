import type { BlockComponentProps } from './types';
import { nestedRecords, text } from './types';

export default function Faq({ props }: BlockComponentProps) {
    return <section className="mx-auto max-w-3xl space-y-8"><h2 className="text-4xl">{text(props, 'title')}</h2><div className="divide-y divide-brand-line border-y border-brand-line">{nestedRecords(props, 'items').map((item, index) => <details key={index} className="group py-5"><summary className="cursor-pointer list-none font-semibold text-brand-navy">{String(item.question ?? '')}</summary><p className="mt-3 text-brand-muted">{String(item.answer ?? '')}</p></details>)}</div></section>;
}
