<?php

namespace App\Policies;

class TagPolicy extends BaseContentPolicy
{
    protected function permissionPrefix(): string
    {
        return 'posts';
    }
}
