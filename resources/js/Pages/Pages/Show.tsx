import { Head, usePage } from '@inertiajs/react';
import BlockRenderer from '../../Components/blocks/BlockRenderer';
import type { CmsBlock } from '../../Components/blocks/types';
import type { SharedPageProps } from '../../types';

interface CmsPage { title: string; excerpt?: string; template: string; seo?: { title?: string; description?: string } | null }
interface ShowProps extends SharedPageProps { page: CmsPage; blocks: CmsBlock[] }

export default function Show() {
    const { page, blocks } = usePage<ShowProps>().props;
    return <><Head title={page.seo?.title || page.title}><meta head-key="description" name="description" content={page.seo?.description || page.excerpt || ''} /></Head>{blocks.length > 0 ? <BlockRenderer blocks={blocks} /> : <section className="py-20 text-center"><p className="text-sm font-semibold uppercase tracking-[0.2em] text-brand-blue">Digify</p><h1 className="mt-4 text-5xl">{page.title}</h1>{page.excerpt && <p className="mx-auto mt-5 max-w-2xl text-lg text-brand-muted">{page.excerpt}</p>}</section>}</>;
}
