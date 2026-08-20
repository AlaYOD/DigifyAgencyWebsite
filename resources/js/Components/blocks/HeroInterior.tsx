import type { BlockComponentProps } from './types';
import { mediaUrl, text } from './types';

export default function HeroInterior({ props }: BlockComponentProps) {
    const image = mediaUrl(props);
    return <section className="grid items-center gap-10 rounded-[2rem] bg-brand-paper p-8 md:grid-cols-2 md:p-12">
        <div className="space-y-5"><p className="text-sm font-semibold uppercase tracking-[0.2em] text-brand-blue">{text(props, 'eyebrow')}</p><h1 className="text-5xl font-bold">{text(props, 'title')}</h1><p className="text-lg text-brand-muted">{text(props, 'body')}</p></div>
        {image && <img src={image} alt={text(props, 'alt')} className="aspect-[4/3] w-full rounded-3xl object-cover" />}
    </section>;
}
