<?php

namespace App\Http\Resources;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Project */
class PublicProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale();

        return [
            'slug' => $this->getTranslation('slug', $locale), 'title' => $this->getTranslation('title', $locale),
            'summary' => $this->getTranslation('summary', $locale), 'client_name' => $this->client_name,
            'sector' => $this->sector, 'discipline' => $this->discipline, 'year' => $this->year,
        ];
    }
}
