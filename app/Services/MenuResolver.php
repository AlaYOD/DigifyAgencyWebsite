<?php

namespace App\Services;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\Post;
use App\Models\Project;

class MenuResolver
{
    public function resolve(Menu $menu, string $locale): array
    {
        $items = $menu->allItems()->with('linkable')->get()->groupBy(
            fn ($item): int => $item instanceof MenuItem ? (int) ($item->parent_id ?? 0) : 0,
        );

        $build = function (int $parentId) use (&$build, $items, $locale): array {
            return ($items[$parentId] ?? collect())->map(fn (MenuItem $item): array => [
                'id' => $item->id,
                'label' => $item->getTranslation('label', $locale),
                'url' => $this->url($item, $locale),
                'target' => $item->target,
                'icon' => $item->icon,
                'children' => $build($item->id),
            ])->values()->all();
        };

        return ['key' => $menu->key, 'name' => $menu->getTranslation('name', $locale), 'items' => $build(0)];
    }

    private function url(MenuItem $item, string $locale): string
    {
        if (filled($item->url)) {
            $url = (string) $item->url;

            if ($locale === 'ar' && str_starts_with($url, '/') && ! str_starts_with($url, '//') && ! str_starts_with($url, '/ar/')) {
                return '/ar'.$url;
            }

            return $url;
        }

        $prefix = $locale === 'ar' ? '/ar' : '';

        return match (true) {
            $item->linkable instanceof Page => $prefix.'/'.ltrim($item->linkable->getTranslation('slug', $locale), '/').'/',
            $item->linkable instanceof Post => $prefix.'/insights/'.ltrim($item->linkable->getTranslation('slug', $locale), '/').'/',
            $item->linkable instanceof Project => $prefix.'/projects/'.ltrim($item->linkable->getTranslation('slug', $locale), '/').'/',
            default => '#',
        };
    }
}
