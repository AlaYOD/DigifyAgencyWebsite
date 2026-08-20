import { createInertiaApp } from '@inertiajs/react';
import createServer from '@inertiajs/react/server';
import { renderToString } from 'react-dom/server';
import type { ComponentType, ReactNode } from 'react';
import AppLayout from './Layouts/AppLayout';

const pages = import.meta.glob('./Pages/**/*.tsx', { eager: true });

createServer((page) => createInertiaApp({
    page,
    render: renderToString,
    resolve: (name) => {
        const module = pages['./Pages/' + name + '.tsx'] as { default: ComponentType } | undefined;

        if (!module) {
            throw new Error('Page not found: ' + name);
        }

        const component = module.default as ComponentType & {
            layout?: (page: ReactNode) => ReactNode;
        };

        component.layout = component.layout ?? ((content) => <AppLayout>{content}</AppLayout>);

        return component;
    },
    setup({ App, props }) {
        return <App {...props} />;
    },
}));
