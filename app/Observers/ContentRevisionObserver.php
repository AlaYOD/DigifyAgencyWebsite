<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;

class ContentRevisionObserver
{
    public function updating(Model $model): void
    {
        if (! method_exists($model, 'revisions') || ! $model->isDirty()) {
            return;
        }

        $payload = collect($model->getFillable())->mapWithKeys(function (string $attribute) use ($model): array {
            $value = $model->getRawOriginal($attribute);
            if (is_string($value) && (in_array($attribute, $model->translatable ?? [], true) || in_array($attribute, ['blocks', 'seo'], true))) {
                $decoded = json_decode($value, true);
                $value = json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
            }

            return [$attribute => $value];
        })->all();

        $model->revisions()->create([
            'user_id' => auth()->id(),
            'label' => 'Before update on '.now()->toDateTimeString(),
            'payload' => $payload,
        ]);
    }
}
