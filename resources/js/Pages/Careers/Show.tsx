import { Head, Link, usePage } from '@inertiajs/react';
import type { SharedPageProps } from '../../types';
import type { CareerJob } from '../../types/careers';

interface ShowProps extends SharedPageProps {
    job: CareerJob;
}

export default function Show() {
    const { job } = usePage<ShowProps>().props;

    return (
        <>
            <Head title={job.meta.title}>
                <meta name="description" content={job.meta.description} />
                <link rel="canonical" href={job.meta.canonical} />
                {Object.entries(job.meta.hreflang).map(([locale, href]) => (
                    <link key={locale} rel="alternate" hrefLang={locale} href={href} />
                ))}
                <script type="application/ld+json">{JSON.stringify(job.json_ld)}</script>
            </Head>
            <article className="mx-auto max-w-3xl space-y-10">
                <header className="space-y-5">
                    <p className="text-sm font-medium uppercase tracking-wide text-slate-500">{job.department.name}</p>
                    <h1 className="text-4xl font-semibold tracking-tight text-brand-navy">{job.title}</h1>
                    <div className="flex flex-wrap gap-x-4 gap-y-2 text-sm text-slate-600">
                        <span>{job.employment_type}</span>
                        <span>{job.workplace_type}</span>
                        {job.city && <span>{job.city}</span>}
                        <span>Posted {job.relative_published_at}</span>
                    </div>
                </header>
                <div className="space-y-8 leading-8 text-slate-700">
                    <section><h2 className="mb-3 text-2xl font-semibold text-brand-navy">Description</h2><p>{job.description}</p></section>
                    <section><h2 className="mb-3 text-2xl font-semibold text-brand-navy">Responsibilities</h2><p>{job.responsibilities}</p></section>
                    <section><h2 className="mb-3 text-2xl font-semibold text-brand-navy">Requirements</h2><p>{job.requirements}</p></section>
                    <section><h2 className="mb-3 text-2xl font-semibold text-brand-navy">Benefits</h2><p>{job.benefits}</p></section>
                </div>
                {job.salary_is_public && (
                    <p className="text-lg font-medium text-brand-navy">
                        Salary: {job.salary_min}–{job.salary_max} {job.salary_currency} / {job.salary_period}
                    </p>
                )}
                <Link href={'/careers/' + job.slug + '/apply/'} className="inline-flex rounded-md bg-brand-navy px-5 py-3 font-medium text-white">
                    Apply
                </Link>
            </article>
        </>
    );
}
