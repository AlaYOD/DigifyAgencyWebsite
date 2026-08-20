<?php

namespace App\Http\Resources;

use App\Models\Post;
use App\Services\HtmlSanitizer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Post */
class PublicPostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale();

        return [
            'slug' => $this->getTranslation('slug', $locale), 'title' => $this->getTranslation('title', $locale),
            'excerpt' => $this->getTranslation('excerpt', $locale), 'body' => app(HtmlSanitizer::class)->sanitize($this->getTranslation('body', $locale)),
            'seo' => $this->getTranslation('seo', $locale), 'published_at' => $this->published_at?->toFormattedDateString(),
            'reading_time' => $this->reading_time, 'category' => $this->category?->getTranslation('name', $locale),
        ];
    }
}
