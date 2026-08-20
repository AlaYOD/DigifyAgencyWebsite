import type { BlockComponentProps } from './types';
import { mediaUrl, text } from './types';

export default function HeroCinematic({ props }: BlockComponentProps) {
    const image = mediaUrl(props);
    return <section className="relative isolate min-h-[70vh] overflow-hidden rounded-[2rem] bg-brand-navy px-6 py-24 text-white sm:px-12">
        {image && <img src={image} alt={text(props, 'alt')} className="absolute inset-0 -z-20 h-full w-full object-cover" />}
        {props.dark_overlay !== false && <div className="absolute inset-0 -z-10 bg-brand-navy/70" />}
        <div className="max-w-3xl space-y-6">
            {text(props, 'eyebrow') && <p className="text-sm font-semibold uppercase tracking-[0.2em] text-brand-yellow">{text(props, 'eyebrow')}</p>}
            <h1 className="text-5xl font-bold tracking-tight text-white md:text-7xl">{text(props, 'title')}</h1>
            {text(props, 'body') && <p className="max-w-2xl text-lg text-white/80">{text(props, 'body')}</p>}
            {text(props, 'cta_url') && <a href={text(props, 'cta_url')} className="inline-flex rounded-full bg-brand-yellow px-6 py-3 font-semibold text-brand-navy">{text(props, 'cta_label') || 'Learn more'}</a>}
        </div>
    </section>;
}
