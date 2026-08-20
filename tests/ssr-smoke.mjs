import { createServer } from 'vite';
import { createElement } from 'react';
import { renderToString } from 'react-dom/server';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { readdirSync } from 'node:fs';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
function pageFiles(directory) {
    return readdirSync(directory, { withFileTypes: true }).flatMap((entry) => {
        const fullPath = path.join(directory, entry.name);
        return entry.isDirectory() ? pageFiles(fullPath) : entry.name.endsWith('.tsx') ? [fullPath] : [];
    });
}
const pages = pageFiles(path.resolve(root, 'resources/js/Pages'));
const vite = await createServer({ root, server: { middlewareMode: true }, appType: 'custom' });
const { createInertiaApp } = await vite.ssrLoadModule('@inertiajs/react');

const shared = {
    locale: 'en', direction: 'ltr',
    locales: [{ code: 'en', native_name: 'English', direction: 'ltr' }, { code: 'ar', native_name: 'العربية', direction: 'rtl' }],
    settings: { site_name: 'Digify', contact_email: 'hello@digify.test' }, flash: { success: null, error: null },
};
const job = {
    id: 1, title: 'Senior Engineer', slug: 'senior-engineer', summary: 'A role', description: 'Description',
    responsibilities: 'Responsibilities', requirements: 'Requirements', benefits: 'Benefits',
    department: { id: 1, name: 'Engineering', sort_order: 1 }, employment_type: 'full_time', workplace_type: 'remote',
    city: 'Ramallah', country_code: 'PS', experience_level: 'senior', positions_count: 1, salary_is_public: false,
    status: 'published', published_at: '2026-01-01T00:00:00Z', closes_at: null, reference_code: 'ENG-2026-001',
    meta: { title: 'Senior Engineer', description: 'A role', canonical: 'http://localhost/careers/senior-engineer/', hreflang: {} }, json_ld: {}, relative_published_at: 'today',
};

function propsFor(name) {
    if (name.endsWith('/Index')) return { ...shared, jobs: [], filters: { employment_type: null, workplace_type: null, department: null } };
    if (name.endsWith('/Show')) return { ...shared, job };
    if (name.endsWith('/Apply')) return { ...shared, job, errors: {}, old: {} };
    return { ...shared, reference_code: 'ENG-2026-001', job };
}

let failed = false;
for (const file of pages) {
    const name = path.relative(path.resolve(root, 'resources/js/Pages'), file).replaceAll(path.sep, '/').replace(/\.tsx$/, '');
    try {
        const module = await vite.ssrLoadModule(file);
        await createInertiaApp({
            page: { component: name, props: propsFor(name), url: '/', version: null },
            resolve: () => module,
            render: renderToString,
            setup: ({ App, props }) => createElement(App, props),
        });
        console.log(`PASS ${name}`);
    } catch (error) {
        failed = true;
        const message = String(error);
        const global = ['window', 'document', 'localStorage', 'IntersectionObserver'].find((item) => message.includes(item)) ?? 'unknown global';
        console.error(`FAIL ${name}: ${global} (${message})`);
    }
}
await vite.close();
if (failed) process.exitCode = 1;
