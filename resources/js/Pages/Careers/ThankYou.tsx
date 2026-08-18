import { Head, Link, usePage } from '@inertiajs/react';
import type { SharedPageProps } from '../../types';

interface ThankYouProps extends SharedPageProps {
    referenceCode: string | null;
}

export default function ThankYou() {
    const { locale, referenceCode } = usePage<ThankYouProps>().props;
    const arabic = locale === 'ar';

    return (
        <>
            <Head title={arabic ? 'شكرًا لتقديمك' : 'Thank you'} />
            <section className="mx-auto max-w-2xl space-y-5 text-center">
                <h1 className="text-4xl font-semibold text-brand-navy">
                    {arabic ? 'شكرًا لتقديمك' : 'Thank you for applying'}
                </h1>
                <p className="text-lg text-slate-600">
                    {arabic
                        ? 'تم استلام طلبك. تم إرسال رسالة تأكيد بالبريد الإلكتروني.'
                        : 'Your application has been received. A confirmation email has been sent.'}
                </p>
                {referenceCode && (
                    <p className="font-medium text-brand-navy">
                        {arabic ? 'المرجع: ' : 'Reference: '}{referenceCode}
                    </p>
                )}
                <Link href={arabic ? '/ar/careers/' : '/careers/'} className="inline-flex rounded-md bg-brand-navy px-5 py-3 font-medium text-white">
                    {arabic ? 'العودة إلى الوظائف' : 'Back to careers'}
                </Link>
            </section>
        </>
    );
}
