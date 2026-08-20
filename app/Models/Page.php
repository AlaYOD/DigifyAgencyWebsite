<?php

namespace App\Models;

use App\Enums\ContentStatus;
use App\Models\Concerns\HasDepartmentVisibility;
use App\Models\Concerns\LogsModelChanges;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

/**
 * @property array<int, array<string, mixed>>|null $blocks
 * @property Carbon|null $published_at
 * @property Carbon|null $updated_at
 */
class Page extends Model implements HasMedia
{
    use HasDepartmentVisibility, HasTranslations, InteractsWithMedia, LogsModelChanges, SoftDeletes;

    public array $translatable = ['slug', 'title', 'excerpt', 'seo'];

    protected $fillable = ['parent_id', 'department_id', 'slug', 'title', 'excerpt', 'blocks', 'seo', 'template', 'status', 'published_at', 'sort_order', 'is_homepage'];

    protected function casts(): array
    {
        return ['blocks' => 'array', 'status' => ContentStatus::class, 'published_at' => 'datetime', 'sort_order' => 'integer', 'is_homepage' => 'boolean'];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ContentStatus::PUBLISHED)->where('published_at', '<=', now());
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
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
