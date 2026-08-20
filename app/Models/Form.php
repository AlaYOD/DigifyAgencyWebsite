<?php

namespace App\Models;

use App\Models\Concerns\HasDepartmentVisibility;
use App\Models\Concerns\LogsModelChanges;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

/**
 * @property array<int, string>|null $notify_emails
 * @property bool $captcha_enabled
 * @property bool $is_active
 * @property-read Collection<int, FormField> $fields
 */
class Form extends Model
{
    use HasDepartmentVisibility, HasTranslations, LogsModelChanges, SoftDeletes;

    public array $translatable = ['name', 'description', 'submit_label', 'success_message'];

    protected $fillable = ['department_id', 'key', 'name', 'description', 'submit_label', 'success_message', 'redirect_url', 'notify_emails', 'webhook_url', 'stores_submissions', 'captcha_enabled', 'retention_days', 'is_active'];

    protected function casts(): array
    {
        return ['notify_emails' => 'array', 'stores_submissions' => 'boolean', 'captcha_enabled' => 'boolean', 'retention_days' => 'integer', 'is_active' => 'boolean'];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function fields(): HasMany
    {
        return $this->hasMany(FormField::class)->orderBy('sort_order');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class);
    }

    public function jobPostings(): HasMany
    {
        return $this->hasMany(JobPosting::class);
    }
}
