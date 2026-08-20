<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RedirectMiss extends Model
{
    protected $fillable = ['path', 'referrer', 'user_agent', 'hits', 'last_seen_at'];

    protected function casts(): array
    {
        return ['hits' => 'integer', 'last_seen_at' => 'datetime'];
    }
}
