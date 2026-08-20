import type { BlockComponentProps } from './types';
import { mediaUrl, text } from './types';

export default function CharacterLoop({ props }: BlockComponentProps) {
    const media = mediaUrl(props);
    return <section className="grid items-center gap-8 rounded-[2rem] bg-brand-yellow p-8 md:grid-cols-2 md:p-12"><div><h2 className="text-4xl">{text(props, 'title')}</h2><p className="mt-4 text-brand-navy/75">{text(props, 'body')}</p></div>{media && <video src={media} aria-label={text(props, 'alt')} className="w-full rounded-3xl" autoPlay muted loop playsInline />}</section>;
}
