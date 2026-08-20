import { useGSAP } from '@gsap/react';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { useRef } from 'react';
import { prefersReducedMotion } from './motion';

const CINEMATIC_EASE = 'cubic-bezier(0.16, 1, 0.3, 1)';

export function useCounterUp<T extends HTMLElement = HTMLElement>(selector = '[data-count]') {
    const scope = useRef<T>(null);
    useGSAP(() => {
        if (typeof window === 'undefined' || prefersReducedMotion()) return;
        gsap.registerPlugin(ScrollTrigger);
        const counters = scope.current?.querySelectorAll<HTMLElement>(selector);
        if (!counters?.length) return;
        counters.forEach((counter) => {
            const end = Number(counter.dataset.count ?? 0);
            const value = { current: 0 };
            gsap.to(value, { current: end, duration: 1.2, ease: CINEMATIC_EASE, scrollTrigger: { trigger: counter, start: 'top 85%' }, onUpdate: () => { counter.textContent = Math.round(value.current).toString(); } });
        });
    }, { scope });
    return scope;
}
