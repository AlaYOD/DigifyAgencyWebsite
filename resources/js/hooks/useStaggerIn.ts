import { useGSAP } from '@gsap/react';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { useRef } from 'react';
import { prefersReducedMotion } from './motion';

const CINEMATIC_EASE = 'cubic-bezier(0.16, 1, 0.3, 1)';

export function useStaggerIn<T extends HTMLElement = HTMLElement>(selector = '[data-reveal-item]') {
    const scope = useRef<T>(null);
    useGSAP(() => {
        if (typeof window === 'undefined' || prefersReducedMotion()) return;
        gsap.registerPlugin(ScrollTrigger);
        const items = scope.current?.querySelectorAll(selector);
        if (!items?.length) return;
        gsap.from(items, { opacity: 0, y: 24, duration: 0.65, stagger: 0.1, ease: CINEMATIC_EASE, scrollTrigger: { trigger: scope.current, start: 'top 85%' } });
    }, { scope });
    return scope;
}
