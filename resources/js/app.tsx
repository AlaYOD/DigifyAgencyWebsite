import './bootstrap';

import { createInertiaApp } from '@inertiajs/react';
import { createRoot } from 'react-dom/client';
import AppLayout from './Layouts/AppLayout';
import type { ComponentType, ReactNode } from 'react';

const pages = import.meta.glob('./Pages/**/*.tsx', { eager: true });

createInertiaApp({
    resolve: (name) => {
        const page = pages['./Pages/' + name + '.tsx'] as { default: ComponentType } | undefined;

        if (!page) {
            throw new Error('Page not found: ' + name);
        }

        const component = page.default as ComponentType & {
            layout?: (page: ReactNode) => ReactNode;
        };

        component.layout = component.layout ?? ((page) => <AppLayout>{page}</AppLayout>);

        return component;
    },
    setup({ el, App, props }) {
        createRoot(el).render(
            <App {...props} />
        );
    },
});
