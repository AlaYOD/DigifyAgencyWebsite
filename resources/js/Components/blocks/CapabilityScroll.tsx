import type { BlockComponentProps } from './types';
import { nestedRecords, text } from './types';

export default function CapabilityScroll({ props }: BlockComponentProps) {
    return <section className="space-y-8"><h2 className="text-4xl">{text(props, 'title')}</h2><div className="flex snap-x gap-5 overflow-x-auto pb-4">{nestedRecords(props, 'items').map((item, index) => <article key={index} className="min-w-[280px] snap-start rounded-3xl border border-brand-line bg-white p-7 sm:min-w-[360px]"><span className="text-2xl text-brand-blue">{String(item.icon ?? '✦')}</span><h3 className="mt-6 text-2xl">{String(item.title ?? '')}</h3><p className="mt-3 text-brand-muted">{String(item.body ?? '')}</p></article>)}</div></section>;
}
