<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationNote extends Model
{
    protected $fillable = ['job_application_id', 'user_id', 'body', 'is_pinned'];

    protected function casts(): array
    {
        return ['is_pinned' => 'boolean'];
    }

    public function jobApplication()
    {
        return $this->belongsTo(JobApplication::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
