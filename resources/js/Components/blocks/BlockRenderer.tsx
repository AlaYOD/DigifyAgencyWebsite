import { blockRegistry } from './registry';
import type { CmsBlock } from './types';

export default function BlockRenderer({ blocks }: { blocks: CmsBlock[] }) {
    return <div className="space-y-20">{blocks.map((block) => {
        const Component = blockRegistry[block.type];
        return Component ? <Component key={block.id} props={block.props} /> : null;
    })}</div>;
}
