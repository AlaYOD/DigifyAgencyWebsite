import type { BlockComponentProps } from './types';
import { mediaUrl, text } from './types';

export default function MediaFull({ props }: BlockComponentProps) {
    const url = mediaUrl(props);
    if (!url) return null;
    return <figure className="space-y-3"><img src={url} alt={text(props, 'alt')} className="max-h-[80vh] w-full rounded-[2rem] object-cover" /><figcaption className="text-sm text-brand-muted">{text(props, 'caption')}</figcaption></figure>;
}
