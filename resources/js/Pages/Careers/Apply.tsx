import { Head, Link, router, usePage } from '@inertiajs/react';
import { zodResolver } from '@hookform/resolvers/zod';
import { useForm } from 'react-hook-form';
import { z } from 'zod';
import type { SharedPageProps } from '../../types';
import type { CareerJob } from '../../types/careers';

interface ApplyProps extends SharedPageProps {
    job: CareerJob;
}

const applicationSchema = z.object({
    first_name: z.string().min(1, 'First name is required.'),
    last_name: z.string().min(1, 'Last name is required.'),
    email: z.string().email('Enter a valid email address.'),
    phone: z.string().optional(),
    cover_letter: z.string().max(2000, 'Cover letter must be 2000 characters or fewer.').optional(),
    portfolio_url: z.string().url('Enter a valid URL.').optional().or(z.literal('')),
    linkedin_url: z.string().url('Enter a valid URL.').optional().or(z.literal('')),
    cv: z.custom<FileList>((value) => typeof FileList !== 'undefined' && value instanceof FileList && value.length === 1, 'A CV is required.')
        .refine((files) => files.item(0)?.type === 'application/pdf' || files.item(0)?.type === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'CV must be a PDF or DOCX file.')
        .refine((files) => (files.item(0)?.size ?? 0) <= 10 * 1024 * 1024, 'CV must be 10 MB or smaller.'),
    website: z.string().optional(),
});

type ApplicationValues = z.infer<typeof applicationSchema>;

export default function Apply() {
    const { job } = usePage<ApplyProps>().props;
    const { register, handleSubmit, formState: { errors, isSubmitting } } = useForm<ApplicationValues>({
        resolver: zodResolver(applicationSchema),
    });

    const submit = (values: ApplicationValues) => {
        const data = new FormData();
        Object.entries(values).forEach(([key, value]) => {
            if (key === 'cv' && value instanceof FileList) {
                data.append('cv', value.item(0) as File);
            } else if (typeof value === 'string') {
                data.append(key, value);
            }
        });

        router.post(window.location.pathname.replace(/\/$/, ''), data, {
            forceFormData: true,
        });
    };

    return (
        <>
            <Head title={'Apply - ' + job.title} />
            <article className="mx-auto max-w-2xl space-y-8">
                <header className="space-y-3">
                    <Link href={'/careers/' + job.slug + '/'} className="text-sm text-slate-500">← {job.title}</Link>
                    <h1 className="text-4xl font-semibold text-brand-navy">Apply for this role</h1>
                    <p className="text-slate-600">{job.department.name}</p>
                </header>
                <form onSubmit={handleSubmit(submit)} className="space-y-6" encType="multipart/form-data">
                    <div className="grid gap-5 md:grid-cols-2">
                        <Field label="First name" error={errors.first_name?.message}><input {...register('first_name')} /></Field>
                        <Field label="Last name" error={errors.last_name?.message}><input {...register('last_name')} /></Field>
                    </div>
                    <Field label="Email" error={errors.email?.message}><input type="email" {...register('email')} /></Field>
                    <Field label="Phone" error={errors.phone?.message}><input {...register('phone')} /></Field>
                    <Field label="Cover letter" error={errors.cover_letter?.message}><textarea rows={6} {...register('cover_letter')} /></Field>
                    <Field label="Portfolio URL" error={errors.portfolio_url?.message}><input type="url" {...register('portfolio_url')} /></Field>
                    <Field label="LinkedIn URL" error={errors.linkedin_url?.message}><input type="url" {...register('linkedin_url')} /></Field>
                    <Field label="CV (PDF or DOCX, max 10 MB)" error={errors.cv?.message}><input type="file" accept=".pdf,.docx,application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document" {...register('cv')} /></Field>
                    <div className="absolute -start-[10000px] h-px w-px overflow-hidden" aria-hidden="true">
                        <label>Website<input tabIndex={-1} autoComplete="off" {...register('website')} /></label>
                    </div>
                    <button disabled={isSubmitting} className="rounded-md bg-brand-navy px-5 py-3 font-medium text-white disabled:opacity-50" type="submit">
                        Submit application
                    </button>
                </form>
            </article>
        </>
    );
}

function Field({ label, error, children }: { label: string; error?: string; children: React.ReactNode }) {
    return (
        <label className="block space-y-2 text-sm font-medium text-slate-700">
            <span>{label}</span>
            {children}
            {error && <span className="block font-normal text-red-600">{error}</span>}
        </label>
    );
}
