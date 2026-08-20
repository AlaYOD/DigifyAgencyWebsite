import { Head, usePage } from '@inertiajs/react';
import BlockRenderer from '../../Components/blocks/BlockRenderer';
import type { CmsBlock } from '../../Components/blocks/types';
import type { SharedPageProps } from '../../types';
interface Project { title: string; summary?: string; client_name: string; sector?: string; discipline?: string; year?: number }
interface Props extends SharedPageProps { project: Project; blocks: CmsBlock[] }
export default function Show() { const { project, blocks } = usePage<Props>().props; return <><Head title={project.title} /><header className="mb-16 grid gap-8 border-b border-brand-line pb-12 md:grid-cols-2"><div><p className="text-sm font-semibold uppercase tracking-wide text-brand-blue">{project.client_name}</p><h1 className="mt-4 text-5xl">{project.title}</h1></div><div><p className="text-lg text-brand-muted">{project.summary}</p><p className="mt-5 text-sm">{[project.sector, project.discipline, project.year].filter(Boolean).join(' · ')}</p></div></header><BlockRenderer blocks={blocks} /></>; }
