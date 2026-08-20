import { Head, usePage } from '@inertiajs/react';
import type { SharedPageProps } from '../../types';
interface Post { title: string; excerpt?: string; body: string; published_at?: string; reading_time: number; category?: string; seo?: { title?: string; description?: string } }
interface Props extends SharedPageProps { post: Post }
export default function Show() { const { post } = usePage<Props>().props; return <><Head title={post.seo?.title || post.title}><meta head-key="description" name="description" content={post.seo?.description || post.excerpt || ''} /></Head><article className="mx-auto max-w-3xl"><header className="mb-12 space-y-4"><p className="text-sm font-semibold uppercase tracking-wide text-brand-blue">{post.category}</p><h1 className="text-5xl">{post.title}</h1><p className="text-brand-muted">{post.published_at} · {post.reading_time} min read</p></header><div className="prose prose-lg" dangerouslySetInnerHTML={{ __html: post.body }} /></article></>; }
