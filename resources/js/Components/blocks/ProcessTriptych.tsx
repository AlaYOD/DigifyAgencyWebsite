import type { BlockComponentProps } from './types';
import { nestedRecords, text } from './types';

export default function ProcessTriptych({ props }: BlockComponentProps) {
    return <section className="space-y-8"><h2 className="text-4xl">{text(props, 'title')}</h2><div className="grid gap-5 md:grid-cols-3">{nestedRecords(props, 'items').map((item, index) => <article key={index} className="rounded-3xl bg-brand-navy p-7 text-white"><span className="text-brand-yellow">0{index + 1}</span><h3 className="mt-8 text-2xl text-white">{String(item.title ?? '')}</h3><p className="mt-3 text-white/70">{String(item.body ?? '')}</p></article>)}</div></section>;
}
