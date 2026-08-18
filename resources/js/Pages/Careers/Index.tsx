import { Head, Link, usePage } from '@inertiajs/react';
import type { SharedPageProps } from '../../types';
import type { CareerJob } from '../../types/careers';

interface IndexProps extends SharedPageProps {
    jobs: CareerJob[];
    filters: Record<string, string | null>;
}

export default function Index() {
    const { jobs, filters } = usePage<IndexProps>().props;
    const groups = jobs.reduce<Record<string, { sort_order: number; jobs: CareerJob[] }>>((result, job) => {
        const key = job.department.name;
        result[key] ??= { sort_order: job.department.sort_order, jobs: [] };
        result[key].jobs.push(job);
        return result;
    }, {});

    return (
        <>
            <Head title="Careers" />
            <div className="space-y-12">
                <header className="max-w-2xl space-y-4">
                    <p className="text-sm font-medium uppercase tracking-wide text-slate-500">Careers</p>
                    <h1 className="text-4xl font-semibold tracking-tight text-brand-navy">Find your next opportunity</h1>
                    <p className="text-lg leading-8 text-slate-600">Join a team building thoughtful digital experiences.</p>
                </header>
                <form method="get" action={typeof window === 'undefined' ? '/careers/' : window.location.pathname} className="grid gap-4 border-y border-slate-200 py-6 md:grid-cols-4">
                    <label className="space-y-2 text-sm">
                        <span className="font-medium">Employment type</span>
                        <select name="employment_type" defaultValue={filters.employment_type ?? ''} className="w-full rounded-md border border-slate-300 p-2">
                            <option value="">All</option>
                            <option value="full_time">Full time</option>
                            <option value="part_time">Part time</option>
                            <option value="contract">Contract</option>
                            <option value="internship">Internship</option>
                            <option value="temporary">Temporary</option>
                        </select>
                    </label>
                    <label className="space-y-2 text-sm">
                        <span className="font-medium">Workplace</span>
                        <select name="workplace_type" defaultValue={filters.workplace_type ?? ''} className="w-full rounded-md border border-slate-300 p-2">
                            <option value="">All</option>
                            <option value="on_site">On site</option>
                            <option value="hybrid">Hybrid</option>
                            <option value="remote">Remote</option>
                        </select>
                    </label>
                    <label className="space-y-2 text-sm">
                        <span className="font-medium">Department</span>
                        <input name="department" defaultValue={filters.department ?? ''} className="w-full rounded-md border border-slate-300 p-2" />
                    </label>
                    <button type="submit" className="self-end rounded-md bg-brand-navy px-4 py-2 font-medium text-white">Filter</button>
                </form>
                {Object.keys(groups).length === 0 ? (
                    <div className="rounded-lg border border-slate-200 p-8 text-center">
                        {/* TODO: move open-application copy to settings. */}
                        <h2 className="text-2xl font-semibold text-brand-navy">No open vacancies</h2>
                        <p className="mt-2 text-slate-600">Send us a general application and tell us how you could contribute.</p>
                    </div>
                ) : (
                    <div className="space-y-12">
                        {Object.entries(groups).sort(([, a], [, b]) => a.sort_order - b.sort_order).map(([department, group]) => (
                            <section key={department} className="space-y-5">
                                <h2 className="text-2xl font-semibold text-brand-navy">{department}</h2>
                                <div className="grid gap-5 md:grid-cols-2">
                                    {group.jobs.map((job) => (
                                        <Link key={job.id} href={'/careers/' + job.slug + '/'} className="rounded-lg border border-slate-200 p-6 transition hover:border-brand-navy">
                                            <h3 className="text-xl font-semibold text-brand-navy">{job.title}</h3>
                                            <p className="mt-2 text-sm text-slate-500">{job.employment_type} · {job.workplace_type}{job.city ? ' · ' + job.city : ''}</p>
                                            <p className="mt-4 text-sm text-slate-600">Posted {job.relative_published_at}</p>
                                        </Link>
                                    ))}
                                </div>
                            </section>
                        ))}
                    </div>
                )}
            </div>
        </>
    );
}
