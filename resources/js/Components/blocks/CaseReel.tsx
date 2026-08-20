import type { BlockComponentProps } from './types';
import { records, text } from './types';

export default function CaseReel({ props }: BlockComponentProps) {
    return <section className="space-y-8"><h2 className="text-4xl">{text(props, 'title')}</h2><div className="grid gap-6 md:grid-cols-2">{records(props, 'projects').map((project) => <a key={String(project.id)} href={`/projects/${String(project.slug)}/`} className="group rounded-3xl border border-brand-line bg-white p-7 transition hover:-translate-y-1 hover:shadow-xl"><p className="text-sm text-brand-muted">{String(project.client_name ?? '')}</p><h3 className="mt-3 text-2xl">{String(project.title ?? '')}</h3><p className="mt-3 text-brand-muted">{String(project.summary ?? '')}</p></a>)}</div></section>;
}
