<?php

namespace App\Models;

use App\Models\Concerns\HasDepartmentVisibility;
use App\Models\Concerns\LogsModelChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Tag extends Model
{
    use HasDepartmentVisibility, HasTranslations, LogsModelChanges, SoftDeletes;

    public array $translatable = ['slug', 'name'];

    protected $fillable = ['department_id', 'slug', 'name'];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class);
    }
}
