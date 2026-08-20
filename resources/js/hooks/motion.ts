export function prefersReducedMotion(): boolean {
    if (typeof window === 'undefined') return false;
    const query = String.fromCharCode(40, 112, 114, 101, 102, 101, 114, 115, 45, 114, 101, 100, 117, 99, 101, 100, 45, 109, 111, 116, 105, 111, 110, 58, 32, 114, 101, 100, 117, 99, 101, 41);
    return window.matchMedia(query).matches;
}
