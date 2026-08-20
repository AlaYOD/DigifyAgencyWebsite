import { Head, usePage } from '@inertiajs/react';
import DynamicForm from '../../Components/DynamicForm';
import type { PublicForm } from '../../Components/DynamicForm';
import type { SharedPageProps } from '../../types';
interface Props extends SharedPageProps { form: PublicForm }
export default function Standalone() { const { form } = usePage<Props>().props; return <><Head title={form.name} /><section className="mx-auto max-w-3xl"><header className="mb-10"><h1 className="text-5xl">{form.name}</h1>{form.description && <p className="mt-4 text-lg text-brand-muted">{form.description}</p>}</header><DynamicForm form={form} /></section></>; }
