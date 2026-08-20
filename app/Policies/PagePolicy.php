<?php

namespace App\Policies;

class PagePolicy extends BaseContentPolicy
{
    protected function permissionPrefix(): string
    {
        return 'pages';
    }
}
