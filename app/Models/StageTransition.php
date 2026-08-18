<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StageTransition extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['job_application_id', 'from_stage_id', 'to_stage_id', 'user_id', 'note', 'created_at'];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    public function save(array $options = []): bool
    {
        if ($this->exists) {
            return false;
        }

        return parent::save($options);
    }

    public function delete(): ?bool
    {
        return false;
    }
}
