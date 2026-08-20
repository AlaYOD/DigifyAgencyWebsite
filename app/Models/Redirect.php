<?php

namespace App\Models;

use App\Models\Concerns\LogsModelChanges;
use Illuminate\Database\Eloquent\Model;

class Redirect extends Model
{
    use LogsModelChanges;

    protected $fillable = ['from_path', 'to_url', 'status_code', 'locale', 'hits', 'last_hit_at', 'is_active'];

    protected function casts(): array
    {
        return ['status_code' => 'integer', 'hits' => 'integer', 'last_hit_at' => 'datetime', 'is_active' => 'boolean'];
    }
}
