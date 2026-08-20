import type { BlockComponentProps } from './types';
import { records, text } from './types';

export default function PostsGrid({ props }: BlockComponentProps) {
    return <section className="space-y-8"><h2 className="text-4xl">{text(props, 'title')}</h2><div className="grid gap-5 md:grid-cols-3">{records(props, 'posts').map((post) => <a key={String(post.id)} href={`/insights/${String(post.slug)}/`} className="rounded-3xl border border-brand-line bg-white p-6"><span className="text-xs font-semibold uppercase tracking-wide text-brand-blue">{String(post.category ?? '')}</span><h3 className="mt-4 text-xl">{String(post.title ?? '')}</h3><p className="mt-3 text-sm text-brand-muted">{String(post.excerpt ?? '')}</p></a>)}</div></section>;
}
