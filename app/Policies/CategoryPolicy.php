<?php

namespace App\Policies;

class CategoryPolicy extends BaseContentPolicy
{
    protected function permissionPrefix(): string
    {
        return 'posts';
    }
}
