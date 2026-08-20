<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/** @property-read Form $form */
class FormSubmission extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['form_id', 'data', 'meta', 'spam_score', 'read_at'];

    protected function casts(): array
    {
        return ['data' => 'array', 'meta' => 'array', 'spam_score' => 'integer', 'read_at' => 'datetime'];
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function jobApplication(): HasOne
    {
        return $this->hasOne(JobApplication::class);
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->hasAnyRole(['ceo', 'hr', 'it', 'content_editor'])) {
            return $query;
        }

        if ($user->hasRole('manager')) {
            $departmentIds = $user->managedDepartments()->pluck('departments.id');

            return $query->whereHas('form', fn (Builder $form): Builder => $form->whereIn('department_id', $departmentIds));
        }

        return $query->whereRaw('1 = 0');
    }
}
