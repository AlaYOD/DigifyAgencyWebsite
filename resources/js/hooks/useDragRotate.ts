import { useGSAP } from '@gsap/react';
import gsap from 'gsap';
import { Draggable } from 'gsap/Draggable';
import { useRef } from 'react';
import { prefersReducedMotion } from './motion';

export function useDragRotate<T extends HTMLElement = HTMLElement>(selector?: string) {
    const scope = useRef<T>(null);
    useGSAP(() => {
        if (typeof window === 'undefined' || prefersReducedMotion()) return;
        gsap.registerPlugin(Draggable);
        const element = selector ? scope.current?.querySelector(selector) : scope.current;
        if (!element) return;
        Draggable.create(element, { type: 'rotation', inertia: false });
    }, { scope });
    return scope;
}
