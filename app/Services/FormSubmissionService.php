<?php

namespace App\Services;

use App\Enums\FormFieldType;
use App\Jobs\DeliverFormSubmission;
use App\Models\Form;
use App\Models\FormSubmission;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class FormSubmissionService
{
    public function submit(Form $form, array $validated, string $ip, ?string $userAgent, ?string $referrer): ?FormSubmission
    {
        $data = [];
        $utm = [];
        parse_str((string) parse_url((string) $referrer, PHP_URL_QUERY), $query);
        foreach (['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'] as $key) {
            if (is_string($query[$key] ?? null)) {
                $utm[$key] = str($query[$key])->limit(255)->toString();
            }
        }

        foreach ($form->fields as $field) {
            if (in_array($field->type, [FormFieldType::HEADING, FormFieldType::PARAGRAPH], true) || ! Arr::has($validated, $field->key)) {
                continue;
            }

            $value = Arr::get($validated, $field->key);
            $data[$field->key] = $value instanceof UploadedFile
                ? $value->store("form-submissions/{$form->id}", 'private')
                : $value;
        }

        $submission = DB::transaction(function () use ($form, $data, $ip, $userAgent, $referrer, $utm): ?FormSubmission {
            if (! $form->stores_submissions) {
                return null;
            }

            $submission = $form->submissions()->create([
                'data' => $data,
                'meta' => [
                    'ip_hash' => hash_hmac('sha256', $ip, (string) config('app.key')),
                    'user_agent' => str($userAgent)->limit(500)->toString(),
                    'referrer' => str($referrer)->limit(1000)->toString(),
                    'locale' => app()->getLocale(),
                    'utm' => $utm,
                ],
            ]);

            return $submission instanceof FormSubmission ? $submission : null;
        });

        DeliverFormSubmission::dispatch($form->id, $submission?->id, $data)->afterResponse();

        return $submission;
    }
}
