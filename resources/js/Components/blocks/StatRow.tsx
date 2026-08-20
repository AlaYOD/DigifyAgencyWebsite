import type { BlockComponentProps } from './types';
import { nestedRecords } from './types';

export default function StatRow({ props }: BlockComponentProps) {
    return <section className="grid gap-px overflow-hidden rounded-3xl bg-brand-line sm:grid-cols-2 lg:grid-cols-4">{nestedRecords(props, 'items').map((item, index) => <div key={`${String(item.value)}-${index}`} className="bg-white p-8"><strong className="block text-4xl text-brand-navy">{String(item.value ?? '')}</strong><span className="mt-2 block text-brand-muted">{String(item.label ?? '')}</span></div>)}</section>;
}
