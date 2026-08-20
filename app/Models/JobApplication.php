<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @property Carbon|null $applied_at
 * @property-read JobPosting $jobPosting
 * @property-read PipelineStage|null $pipelineStage
 */
class JobApplication extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, LogsActivity, SoftDeletes;

    protected $fillable = [
        'job_posting_id', 'form_submission_id', 'pipeline_stage_id', 'first_name', 'last_name', 'email', 'phone',
        'cover_letter', 'portfolio_url', 'linkedin_url', 'locale', 'source', 'ai_score',
        'ai_summary', 'rating', 'is_read', 'applied_at',
    ];

    protected function casts(): array
    {
        return [
            'ai_summary' => 'array',
            'ai_score' => 'integer',
            'rating' => 'integer',
            'is_read' => 'boolean',
            'applied_at' => 'datetime',
        ];
    }

    public function jobPosting(): BelongsTo
    {
        return $this->belongsTo(JobPosting::class)->withTrashed();
    }

    public function pipelineStage(): BelongsTo
    {
        return $this->belongsTo(PipelineStage::class);
    }

    public function formSubmission(): BelongsTo
    {
        return $this->belongsTo(FormSubmission::class);
    }

    public function transitions(): HasMany
    {
        return $this->hasMany(StageTransition::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(ApplicationNote::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cv')
            ->singleFile()
            ->useDisk('private')
            ->acceptsMimeTypes([
                'application/pdf',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ]);
    }

    public function getDisplayNameAttribute(): string
    {
        // Redaction belongs to the Resource and Policy layers, not the model.
        return "{$this->first_name} {$this->last_name}";
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->hasAnyRole(['ceo', 'hr', 'it'])) {
            return $query;
        }

        if ($user->hasRole('manager')) {
            return $query->whereHas('jobPosting', fn (Builder $posting): Builder => $posting
                ->whereIn('department_id', $user->managedDepartments()->pluck('departments.id')));
        }

        return $query->whereRaw('1 = 0');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly([])->dontSubmitEmptyLogs();
    }
}
