# Skill: Models

## Template
```php
class JobPosting extends Model
{
    use HasFactory, SoftDeletes, HasTranslations, InteractsWithMedia, LogsActivity;

    public array $translatable = ['title', 'slug', 'summary', 'description',
                                  'responsibilities', 'requirements', 'benefits'];

    protected $fillable = [/* explicit — never $guarded = [] */];

    protected function casts(): array
    {
        return [
            'employment_type'  => EmploymentType::class,
            'status'           => JobStatus::class,
            'published_at'     => 'datetime',
            'closes_at'        => 'datetime',
            'seo'              => 'array',
            'salary_is_public' => 'boolean',
        ];
    }
}
```

## Rules
- Enums as PHP backed enums in `app/Enums/`, cast in the model.
- Scopes for reusable query logic: `scopePublished`, `scopeVisibleTo`.
- Observers for cache invalidation and counters — never inline in controllers.
- Never send mail or dispatch jobs from a model. That belongs in a Service.

## Department scoping — deny by default
```php
public function scopeVisibleTo(Builder $q, User $user): Builder
{
    if ($user->hasAnyRole(['ceo', 'hr'])) return $q;

    if ($user->hasRole('manager')) {
        return $q->whereHas('jobPosting', fn ($s) =>
            $s->whereIn('department_id', $user->managedDepartments()->pluck('departments.id')));
    }

    return $q->whereRaw('1 = 0');   // REQUIRED — a new role with no rule sees nothing
}
```

## Observer
```php
public function saved(JobPosting $posting): void
{
    Cache::tags(['careers'])->flush();
}
```
