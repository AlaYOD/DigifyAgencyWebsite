import type { BlockComponentProps } from './types';
import { records, text } from './types';

export default function LogoMarquee({ props }: BlockComponentProps) {
    return <section className="space-y-8 overflow-hidden"><h2 className="text-center text-3xl">{text(props, 'title')}</h2><div className="flex flex-wrap items-center justify-center gap-10">{records(props, 'media').map((item) => <img key={String(item.id)} src={String(item.url)} alt={String(item.name ?? '')} className="max-h-12 max-w-40 object-contain grayscale" />)}</div></section>;
}
