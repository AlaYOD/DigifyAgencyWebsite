import { useForm, usePage } from '@inertiajs/react';
import { useEffect, useMemo, useRef } from 'react';
import type { SharedPageProps } from '../types';

interface FormOption { value: string; label: string }
interface ConditionalLogic { field?: string; operator?: 'equals' | 'not_equals' | 'contains'; value?: string }
interface FormField {
    key: string;
    type: 'text' | 'textarea' | 'email' | 'tel' | 'number' | 'date' | 'select' | 'multiselect' | 'radio' | 'checkbox' | 'file' | 'heading' | 'paragraph';
    label: string;
    placeholder?: string;
    help_text?: string;
    options: FormOption[];
    rules: string[];
    conditional_logic?: ConditionalLogic;
    width: 'full' | 'half' | 'third' | 'two_thirds';
}

export interface PublicForm {
    key: string;
    name: string;
    description?: string;
    submit_label: string;
    success_message: string;
    redirect_url?: string;
    action: string;
    captcha_enabled: boolean;
    captcha_site_key?: string;
    fields: FormField[];
}

type FormValue = string | boolean | string[] | File | null;

declare global {
    interface Window {
        turnstile?: { render: (element: HTMLElement, options: { sitekey: string; callback: (token: string) => void; 'expired-callback': () => void }) => string };
    }
}

function Turnstile({ siteKey, onToken }: { siteKey: string; onToken: (token: string) => void }) {
    const container = useRef<HTMLDivElement>(null);

    useEffect(() => {
        let active = true;
        const render = () => {
            if (active && container.current && window.turnstile) {
                window.turnstile.render(container.current, { sitekey: siteKey, callback: onToken, 'expired-callback': () => onToken('') });
            }
        };

        const existing = document.querySelector<HTMLScriptElement>('script[data-digify-turnstile]');
        if (existing) {
            if (window.turnstile) render();
            else existing.addEventListener('load', render, { once: true });
        } else {
            const script = document.createElement('script');
            script.src = 'https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit';
            script.async = true;
            script.defer = true;
            script.dataset.digifyTurnstile = 'true';
            script.addEventListener('load', render, { once: true });
            document.head.appendChild(script);
        }

        return () => { active = false; };
    }, [onToken, siteKey]);

    return <div ref={container} />;
}

export default function DynamicForm({ form }: { form: PublicForm }) {
    const initialValues = useMemo(() => Object.fromEntries(form.fields.filter((field) => !['heading', 'paragraph'].includes(field.type)).map((field) => [field.key, field.type === 'multiselect' ? [] : field.type === 'checkbox' ? false : ''])) as Record<string, FormValue>, [form.fields]);
    const { flash } = usePage<SharedPageProps>().props;
    const { data, setData, post, processing, errors, reset } = useForm<Record<string, FormValue>>({ ...initialValues, _website: '', captcha_token: '' });

    const isVisible = (field: FormField): boolean => {
        const logic = field.conditional_logic;
        if (!logic?.field) return true;
        const actual = data[logic.field];
        if (logic.operator === 'not_equals') return actual !== logic.value;
        if (logic.operator === 'contains') return Array.isArray(actual) ? actual.includes(logic.value ?? '') : String(actual).includes(logic.value ?? '');
        return actual === logic.value;
    };

    const width = (value: FormField['width']): string => ({ full: 'md:col-span-6', half: 'md:col-span-3', third: 'md:col-span-2', two_thirds: 'md:col-span-4' })[value];
    const fieldClass = 'w-full rounded-xl border border-brand-line bg-white px-4 py-3 outline-none transition focus:border-brand-blue focus:ring-2 focus:ring-brand-blue/20';

    return <form onSubmit={(event) => { event.preventDefault(); post(form.action, { preserveScroll: true, onSuccess: () => { reset(); if (form.redirect_url) window.location.assign(form.redirect_url); } }); }} className="grid gap-5 md:grid-cols-6">
        <input type="text" name="_website" value={String(data._website)} onChange={(event) => setData('_website', event.target.value)} tabIndex={-1} autoComplete="off" className="sr-only" aria-hidden="true" />
        {form.fields.map((field) => {
            if (!isVisible(field)) return null;
            if (field.type === 'heading') return <h3 key={field.key} className="md:col-span-6 text-2xl">{field.label}</h3>;
            if (field.type === 'paragraph') return <p key={field.key} className="md:col-span-6 text-brand-muted">{field.help_text || field.label}</p>;
            const required = field.rules.includes('required');
            const error = errors[field.key];

            return <label key={field.key} className={`${width(field.width)} space-y-2`}>
                <span className="block text-sm font-semibold text-brand-navy">{field.label}{required && <span className="text-red-600"> *</span>}</span>
                {field.type === 'textarea' ? <textarea value={String(data[field.key] ?? '')} onChange={(event) => setData(field.key, event.target.value)} placeholder={field.placeholder} className={fieldClass} rows={5} />
                    : field.type === 'select' ? <select value={String(data[field.key] ?? '')} onChange={(event) => setData(field.key, event.target.value)} className={fieldClass}><option value="">—</option>{field.options.map((option) => <option key={option.value} value={option.value}>{option.label}</option>)}</select>
                    : field.type === 'multiselect' ? <select multiple value={Array.isArray(data[field.key]) ? data[field.key] as string[] : []} onChange={(event) => setData(field.key, Array.from(event.target.selectedOptions).map((option) => option.value))} className={fieldClass}>{field.options.map((option) => <option key={option.value} value={option.value}>{option.label}</option>)}</select>
                    : field.type === 'radio' ? <span className="flex flex-wrap gap-4">{field.options.map((option) => <label key={option.value} className="flex items-center gap-2"><input type="radio" name={field.key} value={option.value} checked={data[field.key] === option.value} onChange={() => setData(field.key, option.value)} /> {option.label}</label>)}</span>
                    : field.type === 'checkbox' ? <input type="checkbox" checked={Boolean(data[field.key])} onChange={(event) => setData(field.key, event.target.checked)} className="size-5 rounded border-brand-line" />
                    : field.type === 'file' ? <input type="file" onChange={(event) => setData(field.key, event.target.files?.[0] ?? null)} className={fieldClass} />
                    : <input type={field.type} value={String(data[field.key] ?? '')} onChange={(event) => setData(field.key, event.target.value)} placeholder={field.placeholder} className={fieldClass} />}
                {field.help_text && <span className="block text-xs text-brand-muted">{field.help_text}</span>}
                {error && <span className="block text-sm text-red-600">{error}</span>}
            </label>;
        })}
        {form.captcha_enabled && form.captcha_site_key && <div className="md:col-span-6"><Turnstile siteKey={form.captcha_site_key} onToken={(token) => setData('captcha_token', token)} />{errors.captcha_token && <p className="text-sm text-red-600">{errors.captcha_token}</p>}</div>}
        <div className="md:col-span-6"><button type="submit" disabled={processing} className="rounded-full bg-brand-navy px-7 py-3 font-semibold text-white disabled:opacity-50">{processing ? '…' : form.submit_label}</button></div>
        {flash.form_success && <p role="status" className="md:col-span-6 rounded-xl bg-emerald-50 p-4 text-emerald-800">{flash.form_success}</p>}
    </form>;
}
