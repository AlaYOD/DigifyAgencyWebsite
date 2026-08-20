<?php

namespace App\Models;

use App\Enums\ContentStatus;
use App\Models\Concerns\HasDepartmentVisibility;
use App\Models\Concerns\LogsModelChanges;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

/**
 * @property Carbon|null $published_at
 * @property-read Category|null $category
 */
class Post extends Model implements HasMedia
{
    use HasDepartmentVisibility, HasTranslations, InteractsWithMedia, LogsModelChanges, SoftDeletes;

    public array $translatable = ['slug', 'title', 'excerpt', 'body', 'seo'];

    protected $fillable = ['category_id', 'author_id', 'department_id', 'slug', 'title', 'excerpt', 'body', 'seo', 'status', 'published_at', 'views_count', 'reading_time', 'is_featured'];

    protected function casts(): array
    {
        return ['status' => ContentStatus::class, 'published_at' => 'datetime', 'views_count' => 'integer', 'reading_time' => 'integer', 'is_featured' => 'boolean'];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ContentStatus::PUBLISHED)->where('published_at', '<=', now());
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function revisions(): MorphMany
    {
        return $this->morphMany(Revision::class, 'revisionable');
    }
}
