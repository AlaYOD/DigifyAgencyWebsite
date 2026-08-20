import { useGSAP } from '@gsap/react';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { useRef } from 'react';
import { prefersReducedMotion } from './motion';

const CINEMATIC_EASE = 'cubic-bezier(0.16, 1, 0.3, 1)';

export function useScrollReveal<T extends HTMLElement = HTMLElement>(target?: string) {
    const scope = useRef<T>(null);
    useGSAP(() => {
        if (typeof window === 'undefined' || prefersReducedMotion()) return;
        gsap.registerPlugin(ScrollTrigger);
        const element = target ? scope.current?.querySelectorAll(target) : scope.current;
        if (!element) return;
        gsap.from(element, { opacity: 0, y: 40, duration: 0.8, ease: CINEMATIC_EASE, scrollTrigger: { trigger: scope.current, start: 'top 85%' } });
    }, { scope });
    return scope;
}
