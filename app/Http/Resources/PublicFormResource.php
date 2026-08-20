<?php

namespace App\Http\Resources;

use App\Models\Form;
use App\Models\FormField;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Form */
class PublicFormResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale();

        return [
            'key' => $this->key,
            'name' => $this->getTranslation('name', $locale),
            'description' => $this->getTranslation('description', $locale),
            'submit_label' => $this->getTranslation('submit_label', $locale),
            'success_message' => $this->getTranslation('success_message', $locale),
            'redirect_url' => $this->redirect_url,
            'action' => route(app()->getLocale() === 'ar' ? 'forms.ar.submit' : 'forms.submit', $this->resource, false),
            'captcha_enabled' => $this->captcha_enabled,
            'captcha_site_key' => $this->captcha_enabled ? config('services.turnstile.site_key') : null,
            'fields' => $this->fields->map(fn (FormField $field): array => [
                'key' => $field->key,
                'type' => $field->type->value,
                'label' => $field->getTranslation('label', $locale),
                'placeholder' => $field->getTranslation('placeholder', $locale),
                'help_text' => $field->getTranslation('help_text', $locale),
                'options' => collect($field->options ?? [])->map(fn (array $option): array => [
                    'value' => $option['value'] ?? '',
                    'label' => $option['label'][$locale] ?? $option['label']['en'] ?? $option['value'] ?? '',
                ])->values()->all(),
                'rules' => $field->rules ?? [],
                'conditional_logic' => $field->conditional_logic,
                'width' => $field->width,
            ])->values()->all(),
        ];
    }
}
