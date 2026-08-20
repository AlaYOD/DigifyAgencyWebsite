<?php

namespace App\Http\Resources;

use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Page */
class PublicPageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale();

        return [
            'id' => $this->id,
            'slug' => $this->getTranslation('slug', $locale),
            'title' => $this->getTranslation('title', $locale),
            'excerpt' => $this->getTranslation('excerpt', $locale),
            'template' => $this->template,
            'seo' => $this->getTranslation('seo', $locale),
            'updated_at' => $this->updated_at?->toAtomString(),
        ];
    }
}
