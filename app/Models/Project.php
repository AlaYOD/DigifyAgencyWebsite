<?php

namespace App\Models;

use App\Enums\ContentStatus;
use App\Models\Concerns\HasDepartmentVisibility;
use App\Models\Concerns\LogsModelChanges;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

class Project extends Model implements HasMedia
{
    use HasDepartmentVisibility, HasTranslations, InteractsWithMedia, LogsModelChanges, SoftDeletes;

    public array $translatable = ['slug', 'title', 'summary'];

    protected $fillable = ['department_id', 'slug', 'client_name', 'title', 'summary', 'blocks', 'sector', 'discipline', 'year', 'cover_media_id', 'is_featured', 'sort_order', 'status', 'published_at'];

    protected function casts(): array
    {
        return ['blocks' => 'array', 'year' => 'integer', 'sort_order' => 'integer', 'is_featured' => 'boolean', 'status' => ContentStatus::class, 'published_at' => 'datetime'];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ContentStatus::PUBLISHED)->where('published_at', '<=', now());
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function revisions(): MorphMany
    {
        return $this->morphMany(Revision::class, 'revisionable');
    }
}
