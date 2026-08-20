import type { ComponentType } from 'react';

export interface CmsBlock {
    id: string;
    type: string;
    props: Record<string, unknown>;
}

export interface BlockComponentProps {
    props: Record<string, unknown>;
}

export type BlockComponent = ComponentType<BlockComponentProps>;

export function text(props: Record<string, unknown>, key: string): string {
    return typeof props[key] === 'string' ? props[key] : '';
}

export function numberValue(props: Record<string, unknown>, key: string, fallback = 0): number {
    return typeof props[key] === 'number' ? props[key] : Number(props[key] ?? fallback);
}

export function records(props: Record<string, unknown>, key: string): Array<Record<string, unknown>> {
    return Array.isArray(props[key]) ? (props[key] as Array<Record<string, unknown>>) : [];
}

export function nestedRecords(props: Record<string, unknown>, key: string): Array<Record<string, unknown>> {
    return records(props, key).map((item) => {
        const data = item.data;
        return typeof data === 'object' && data !== null ? data as Record<string, unknown> : item;
    });
}

export function mediaUrl(props: Record<string, unknown>): string {
    const media = props.media;
    if (typeof media === 'object' && media !== null && 'url' in media && typeof media.url === 'string') {
        return media.url;
    }

    return text(props, 'media_url');
}
