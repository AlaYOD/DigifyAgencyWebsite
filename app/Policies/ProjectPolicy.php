<?php

namespace App\Policies;

class ProjectPolicy extends BaseContentPolicy
{
    protected function permissionPrefix(): string
    {
        return 'projects';
    }
}
