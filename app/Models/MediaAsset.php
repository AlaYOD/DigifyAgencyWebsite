<?php

namespace App\Models;

use App\Models\Concerns\LogsModelChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

class MediaAsset extends Model implements HasMedia
{
    use HasTranslations, InteractsWithMedia, LogsModelChanges, SoftDeletes;

    public array $translatable = ['alt_text', 'caption'];

    protected $fillable = ['name', 'alt_text', 'caption', 'credit', 'folder', 'uploaded_by'];

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('default')->singleFile()->useDisk('public');
    }
}
