import DynamicForm from '../DynamicForm';
import type { PublicForm } from '../DynamicForm';
import type { BlockComponentProps } from './types';
import { text } from './types';

export default function FormBlock({ props }: BlockComponentProps) {
    const form = typeof props.form === 'object' && props.form !== null ? props.form as unknown as PublicForm : null;
    if (!form) return null;
    return <section className="rounded-[2rem] bg-brand-paper p-8 md:p-12"><div className="mb-8 max-w-2xl"><h2 className="text-4xl">{text(props, 'title') || form.name}</h2>{form.description && <p className="mt-3 text-brand-muted">{form.description}</p>}</div><DynamicForm form={form} /></section>;
}
