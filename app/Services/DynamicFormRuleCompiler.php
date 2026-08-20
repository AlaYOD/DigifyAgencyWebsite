<?php

namespace App\Services;

use App\Enums\FormFieldType;
use App\Models\FormField;

class DynamicFormRuleCompiler
{
    private const ALLOWED_RULES = ['required', 'nullable', 'sometimes', 'string', 'email', 'numeric', 'integer', 'date', 'boolean', 'array', 'accepted', 'url', 'min', 'max', 'size', 'between', 'digits', 'regex', 'mimes', 'mimetypes', 'extensions'];

    public function compile(FormField $field, array $input): array
    {
        $logic = is_array($field->conditional_logic) ? $field->conditional_logic : null;

        if (! $this->conditionMatches($logic, $input)) {
            return ['exclude'];
        }

        $rules = collect($field->rules ?? [])->filter(function (mixed $rule): bool {
            return in_array(str($rule)->before(':')->toString(), self::ALLOWED_RULES, true);
        })->values()->all();

        $fieldType = $field->type;

        $typeRule = match ($fieldType) {
            FormFieldType::EMAIL => 'email',
            FormFieldType::NUMBER => 'numeric',
            FormFieldType::DATE => 'date',
            FormFieldType::MULTISELECT => 'array',
            FormFieldType::CHECKBOX => 'accepted',
            FormFieldType::FILE => 'file',
            default => 'string',
        };

        if ($fieldType === FormFieldType::FILE) {
            $rules[] = 'file';
            $rules[] = 'max:10240';
        } elseif (! in_array($typeRule, $rules, true)) {
            $rules[] = $typeRule;
        }

        if (in_array($fieldType, [FormFieldType::SELECT, FormFieldType::RADIO], true)) {
            $values = collect($field->options ?? [])->pluck('value')->filter()->implode(',');
            if ($values !== '') {
                $rules[] = 'in:'.$values;
            }
        }

        return array_values(array_unique($rules));
    }

    private function conditionMatches(?array $logic, array $input): bool
    {
        if (blank($logic['field'] ?? null)) {
            return true;
        }

        $actual = data_get($input, $logic['field']);
        $expected = $logic['value'] ?? null;

        return match ($logic['operator'] ?? 'equals') {
            'not_equals' => $actual != $expected,
            'contains' => is_array($actual) ? in_array($expected, $actual, true) : str_contains((string) $actual, (string) $expected),
            default => $actual == $expected,
        };
    }
}
