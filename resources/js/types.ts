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
}
