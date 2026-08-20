import type { BlockComponentProps } from './types';
import { text } from './types';

export default function RichText({ props }: BlockComponentProps) {
    return <section className="prose prose-lg mx-auto max-w-3xl text-brand-text" dangerouslySetInnerHTML={{ __html: text(props, 'content') }} />;
}
