import type { BlockComponentProps } from './types';
import { records, text } from './types';

export default function JobsList({ props }: BlockComponentProps) {
    return <section className="space-y-8"><h2 className="text-4xl">{text(props, 'title')}</h2><div className="divide-y divide-brand-line rounded-3xl border border-brand-line bg-white">{records(props, 'jobs').map((job) => <a key={String(job.id)} href={`/careers/${String(job.slug)}/`} className="flex flex-col gap-3 p-6 transition hover:bg-brand-paper sm:flex-row sm:items-center sm:justify-between"><div><h3 className="text-xl">{String(job.title ?? '')}</h3><p className="text-sm text-brand-muted">{String(job.department ?? '')} · {String(job.workplace_type ?? '')}</p></div><span className="font-semibold text-brand-blue">View role →</span></a>)}</div></section>;
}
