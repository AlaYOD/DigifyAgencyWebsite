import type { PageProps } from '@inertiajs/core';

export interface SharedPageProps extends PageProps {
    locale: string;
    direction: 'ltr' | 'rtl';
    locales: Array<{
        code: string;
        native_name: string;
        direction: 'ltr' | 'rtl';
    }>;
    settings: {
        site_name: string;
        contact_email: string;
    };
    flash: {
        success?: string;
        error?: string;
        form_success?: string;
    };
    menus: Record<string, {
        key: string;
        name: string;
        items: Array<{
            id: number;
            label: string;
            url: string;
            target: 'same' | 'new';
            icon?: string;
            children: Array<unknown>;
        }>;
    }>;
}
