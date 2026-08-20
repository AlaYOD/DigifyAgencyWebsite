<?php

namespace App\Models;

use App\Enums\EmploymentType;
use App\Enums\ExperienceLevel;
use App\Enums\JobStatus;
use App\Enums\SalaryPeriod;
use App\Enums\WorkplaceType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

/**
 * @property EmploymentType|null $employment_type
 * @property WorkplaceType|null $workplace_type
 * @property ExperienceLevel|null $experience_level
 * @property SalaryPeriod|null $salary_period
 * @property JobStatus $status
 * @property Carbon|null $published_at
 * @property Carbon|null $closes_at
 * @property-read Department|null $department
 * @property-read Form|null $form
 */
class JobPosting extends Model implements HasMedia
{
    use HasFactory, HasTranslations, InteractsWithMedia, LogsActivity, SoftDeletes;

    public array $translatable = [
        'title', 'slug', 'summary', 'description', 'responsibilities', 'requirements', 'benefits',
    ];

    protected $fillable = [
        'department_id', 'form_id', 'reference_code', 'title', 'slug', 'summary', 'description',
        'responsibilities', 'requirements', 'benefits', 'employment_type', 'workplace_type',
        'city', 'country_code', 'experience_level', 'experience_years_min', 'salary_min',
        'salary_max', 'salary_currency', 'salary_period', 'salary_is_public', 'positions_count',
        'status', 'published_at', 'closes_at', 'is_featured', 'views_count', 'applications_count', 'seo',
    ];

    protected function casts(): array
    {
        return [
            'employment_type' => EmploymentType::class,
            'workplace_type' => WorkplaceType::class,
            'experience_level' => ExperienceLevel::class,
            'salary_period' => SalaryPeriod::class,
            'status' => JobStatus::class,
            'published_at' => 'datetime',
            'closes_at' => 'datetime',
            'seo' => 'array',
            'salary_is_public' => 'boolean',
            'is_featured' => 'boolean',
            'views_count' => 'integer',
            'applications_count' => 'integer',
            'positions_count' => 'integer',
            'experience_years_min' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (JobPosting $posting): void {
            $department = Department::findOrFail($posting->department_id);
            Department::query()->whereKey($department->id)->lockForUpdate()->firstOrFail();
            $departmentName = $department->getTranslation('name', 'en');
            $prefix = substr(preg_replace('/[^A-Z0-9]/', '', strtoupper($departmentName)), 0, 3);
            $year = now()->year;
            JobPosting::withTrashed()
                ->where('department_id', $posting->department_id)
                ->where('reference_code', 'like', "$prefix-$year-%")
                ->lockForUpdate()
                ->get(['id']);

            $maxSequence = (int) JobPosting::withTrashed()
                ->where('department_id', $posting->department_id)
                ->where('reference_code', 'like', "$prefix-$year-%")
                ->selectRaw("COALESCE(MAX(CAST(SUBSTRING(reference_code FROM '[0-9]{3}$') AS INTEGER)), 0) AS sequence")
                ->value('sequence');

            $posting->reference_code = sprintf('%s-%d-%03d', $prefix, $year, $maxSequence + 1);
        });
    }

    public function save(array $options = []): bool
    {
        if ($this->exists) {
            return parent::save($options);
        }

        return DB::transaction(fn (): bool => parent::save($options));
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', JobStatus::PUBLISHED)
            ->where('published_at', '<=', now())
            ->where(fn (Builder $query): Builder => $query
                ->whereNull('closes_at')
                ->orWhere('closes_at', '>', now()));
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->hasAnyRole(['ceo', 'hr', 'it'])) {
            return $query;
        }

        if ($user->hasRole('manager')) {
            return $query->whereIn('department_id', $user->managedDepartments()->pluck('departments.id'));
        }

        return $query->whereRaw('1 = 0');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly([])->dontSubmitEmptyLogs();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('default');
    }
}
