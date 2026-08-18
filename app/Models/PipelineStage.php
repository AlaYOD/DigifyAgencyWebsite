<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Translatable\HasTranslations;

class PipelineStage extends Model
{
    use HasTranslations;

    public $timestamps = false;

    public array $translatable = ['name'];

    protected $fillable = ['key', 'name', 'color', 'sort_order', 'is_default', 'is_terminal', 'outcome'];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_default' => 'boolean',
            'is_terminal' => 'boolean',
        ];
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order');
    }
}
