import { Link, usePage } from '@inertiajs/react';
import type { PropsWithChildren } from 'react';
import { useEffect } from 'react';
import type { SharedPageProps } from '../types';

export default function AppLayout({ children }: PropsWithChildren) {
    const { locale, direction, locales, settings } = usePage<SharedPageProps>().props;
    const currentLocale = locales.find((item) => item.code === locale);
    const otherLocale = locales.find((item) => item.code !== locale) ?? currentLocale;
    const currentPath = typeof window === 'undefined' ? '/' : window.location.pathname;
    const pathParts = currentPath.split('/').filter(Boolean);
    const pathWithoutLocale = ['en', 'ar'].includes(pathParts[0] ?? '')
        ? pathParts.slice(1)
        : pathParts;
    const languagePath = '/' + (otherLocale?.code ?? 'en') + '/' + pathWithoutLocale.join('/') + (currentPath.endsWith('/') ? '/' : '');

    useEffect(() => {
        document.documentElement.lang = locale;
        document.documentElement.dir = direction;
    }, [direction, locale]);

    return (
        <div className="min-h-screen bg-white text-slate-800">
            <header className="border-b border-slate-200">
                <div className="mx-auto flex max-w-6xl items-center justify-between gap-6 px-6 py-5">
                    <Link href="/" className="text-xl font-semibold tracking-tight text-brand-navy">
                        {settings.site_name}
                    </Link>
                    <nav className="flex items-center gap-6 text-sm font-medium">
                        <Link href="/careers/" className="text-slate-700 hover:text-brand-navy">
                            Careers
                        </Link>
                        <a href={languagePath} className="text-slate-700 hover:text-brand-navy">
                            {otherLocale?.native_name}
                        </a>
                    </nav>
                </div>
            </header>
            <main className="mx-auto max-w-6xl px-6 py-16">
                {children}
            </main>
            <footer className="border-t border-slate-200">
                <div className="mx-auto max-w-6xl px-6 py-8 text-sm text-slate-500">
                    {settings.site_name}
                </div>
            </footer>
        </div>
    );
}
