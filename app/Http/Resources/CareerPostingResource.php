<?php

namespace App\Http\Resources;

use App\Models\JobPosting;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin JobPosting */
class CareerPostingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale();

        return [
            'id' => $this->id,
            'slug' => $this->getTranslation('slug', $locale),
            'title' => $this->getTranslation('title', $locale),
            'summary' => $this->getTranslation('summary', $locale),
            'description' => $this->getTranslation('description', $locale),
            'responsibilities' => $this->getTranslation('responsibilities', $locale),
            'requirements' => $this->getTranslation('requirements', $locale),
            'benefits' => $this->getTranslation('benefits', $locale),
            'reference_code' => $this->reference_code,
            'department' => [
                'name' => $this->department?->getTranslation('name', $locale),
                'sort_order' => $this->department?->sort_order,
            ],
            'employment_type' => $this->employment_type?->value,
            'workplace_type' => $this->workplace_type?->value,
            'city' => $this->city,
            'country_code' => $this->country_code,
            'salary_min' => $this->salary_is_public ? $this->salary_min : null,
            'salary_max' => $this->salary_is_public ? $this->salary_max : null,
            'salary_currency' => $this->salary_is_public ? $this->salary_currency : null,
            'salary_period' => $this->salary_is_public ? $this->salary_period?->value : null,
            'salary_is_public' => $this->salary_is_public,
            'published_at' => $this->published_at?->toIso8601String(),
            'closes_at' => $this->closes_at?->toIso8601String(),
            'relative_published_at' => $this->published_at?->diffForHumans(),
            'json_ld' => $this->when($request->routeIs('careers.show', 'careers.ar.show'), $this->jsonLd()),
            'meta' => $this->meta(),
        ];
    }

    private function jsonLd(): array
    {
        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'JobPosting',
            'title' => $this->getTranslation('title', app()->getLocale()),
            'description' => strip_tags($this->getTranslation('description', app()->getLocale())),
            'datePosted' => $this->published_at?->toIso8601String(),
            'validThrough' => $this->closes_at?->toIso8601String(),
            'employmentType' => $this->employment_type?->schemaValue(),
            'hiringOrganization' => [
                '@type' => 'Organization',
                'name' => 'Digify',
                'sameAs' => config('app.url'),
            ],
            'jobLocation' => [
                '@type' => 'Place',
                'address' => [
                    '@type' => 'PostalAddress',
                    'addressLocality' => $this->city,
                    'addressCountry' => $this->country_code,
                ],
            ],
        ];

        if ($this->salary_is_public) {
            $data['baseSalary'] = [
                '@type' => 'MonetaryAmount',
                'currency' => $this->salary_currency,
                'value' => [
                    '@type' => 'QuantitativeValue',
                    'minValue' => $this->salary_min,
                    'maxValue' => $this->salary_max,
                    'unitText' => $this->salary_period?->value,
                ],
            ];
        }

        return $data;
    }

    private function meta(): array
    {
        $locale = app()->getLocale();
        $englishSlug = $this->getTranslation('slug', 'en');
        $arabicSlug = $this->getTranslation('slug', 'ar');
        $prefix = $locale === 'ar' ? '/ar' : '';
        $baseUrl = rtrim(config('app.url'), '/');

        return [
            'title' => $this->getTranslation('title', $locale),
            'description' => $this->getTranslation('summary', $locale),
            'canonical' => $baseUrl.$prefix.'/careers/'.$this->getTranslation('slug', $locale).'/',
            'hreflang' => [
                'en' => $baseUrl.'/careers/'.$englishSlug.'/',
                'ar' => $baseUrl.'/ar/careers/'.$arabicSlug.'/',
                'x-default' => $baseUrl.'/careers/'.$englishSlug.'/',
            ],
        ];
    }
}
