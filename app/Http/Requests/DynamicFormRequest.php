<?php

namespace App\Http\Requests;

use App\Enums\FormFieldType;
use App\Models\Form;
use App\Models\FormField;
use App\Services\DynamicFormRuleCompiler;
use Illuminate\Foundation\Http\FormRequest;

class DynamicFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->form()->is_active;
    }

    public function rules(): array
    {
        if (filled($this->input('_website'))) {
            return ['_website' => ['nullable', 'string']];
        }

        $compiler = app(DynamicFormRuleCompiler::class);
        $rules = ['_website' => ['nullable', 'max:0'], 'captcha_token' => [$this->form()->captcha_enabled ? 'required' : 'nullable', 'string']];

        foreach ($this->form()->fields as $field) {
            $fieldType = $field->type;

            if (in_array($fieldType, [FormFieldType::HEADING, FormFieldType::PARAGRAPH], true)) {
                continue;
            }

            $rules[$field->key] = $compiler->compile($field, $this->all());

            if ($fieldType === FormFieldType::MULTISELECT) {
                $values = collect($field->options ?? [])->pluck('value')->filter()->implode(',');
                if ($values !== '') {
                    $rules[$field->key.'.*'] = ['in:'.$values];
                }
            }
        }

        return $rules;
    }

    public function attributes(): array
    {
        return $this->form()->fields->mapWithKeys(fn (FormField $field): array => [$field->key => $field->getTranslation('label', app()->getLocale())])->all();
    }

    public function form(): Form
    {
        return $this->route('form')->loadMissing('fields');
    }
}
