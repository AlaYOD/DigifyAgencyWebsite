<?php

namespace App\Policies;

class PostPolicy extends BaseContentPolicy
{
    protected function permissionPrefix(): string
    {
        return 'posts';
    }
}
