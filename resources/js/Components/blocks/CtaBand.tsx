import type { BlockComponentProps } from './types';
import { text } from './types';

export default function CtaBand({ props }: BlockComponentProps) {
    const theme = text(props, 'theme');
    const colors = theme === 'coral' ? 'bg-brand-orange text-brand-navy' : theme === 'white' ? 'bg-white text-brand-navy border border-brand-line' : 'bg-brand-navy text-white';
    return <section className={`flex flex-col gap-8 rounded-[2rem] p-8 md:flex-row md:items-center md:justify-between md:p-12 ${colors}`}><div><h2 className={`text-4xl ${theme === 'navy' || !theme ? 'text-white' : ''}`}>{text(props, 'title')}</h2><p className="mt-3 max-w-2xl opacity-75">{text(props, 'body')}</p></div><a href={text(props, 'cta_url')} className="shrink-0 rounded-full bg-brand-yellow px-6 py-3 font-semibold text-brand-navy">{text(props, 'cta_label')}</a></section>;
}
